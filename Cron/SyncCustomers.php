<?php
declare(strict_types=1);

namespace Ortto\Connector\Cron;

use Ortto\Connector\Api\OrttoClientInterface;
use Ortto\Connector\Api\ConfigurationReaderInterface;
use Ortto\Connector\Api\ImportResponseInterface;
use Ortto\Connector\Api\SyncCategoryInterface as SyncCategory;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Api\SyncJobRepositoryInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Model\OrttoException;
use Ortto\Connector\Model\ImportResponse;
use Ortto\Connector\Model\Scope;
use Exception;
use Ortto\Connector\Api\JobStatusInterface as Status;
use JsonException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;

class SyncCustomers
{
    private OrttoLoggerInterface $logger;
    private OrttoClientInterface $orttoClient;
    private ScopeManagerInterface $scopeManager;
    private Data $helper;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private CustomerRepositoryInterface $customerRepository;
    private ConfigurationReaderInterface $config;
    private SyncJobRepositoryInterface $jobRepository;

    const PAGE_SIZE = 100;

    public function __construct(
        OrttoLoggerInterface $logger,
        OrttoClientInterface $orttoClient,
        ScopeManagerInterface $scopeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $customerRepository,
        ConfigurationReaderInterface $config,
        Data $helper,
        SyncJobRepositoryInterface $jobRepository
    ) {
        $this->logger = $logger;
        $this->orttoClient = $orttoClient;
        $this->scopeManager = $scopeManager;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepository = $customerRepository;
        $this->config = $config;
        $this->jobRepository = $jobRepository;
    }

    /**
     * Sync customers with Ortto
     *
     * @return void
     */
    public function execute(): void
    {
        $category = SyncCategory::CUSTOMER;
        $this->logger->debug("Running customer synchronization CRON job");
        /**
         * @var ScopeConfigInterface[]
         */
        $processedScopes = [];

        $now = $this->helper->nowUTC();

        $jobs = $this->jobRepository->getQueuedJobs($category);
        if (empty($jobs)) {
            $this->logger->debug("No customer sync job was queued");
        } else {
            foreach ($jobs as $job) {
                $jobId = $job->getEntityId();
                $this->logger->info(sprintf('Processing customer synchronization job ID %s', $jobId));
                try {
                    $scope = $this->scopeManager->initialiseScope($job->getScopeType(), $job->getScopeId());
                    if (!$scope->isExplicitlyConnected()) {
                        $this->logger->warn("Job scope is not connected to Ortto", $scope->toArray());
                        $this->jobRepository->markAsFailed($jobId, "Not connected to Ortto");
                        continue;
                    }
                    $this->jobRepository->markAsInProgress($jobId);
                    $result = $this->exportAllCustomers($scope, $jobId);
                    $processedScopes[] = $scope;
                    $this->jobRepository->markAsDone($jobId, $result->toJSON());
                    $this->helper->createCheckpointCollection()->setCheckpoint($category, $now, $scope);
                    $total = $result->getUpdatedTotal() + $result->getCreatedTotal();
                    if ($total > 0) {
                        $this->logger->info(
                            sprintf(
                                "%d customer(s) have been manually exported. Checkpoint's been updated to %s.",
                                $total,
                                $now->format(Config::DB_DATE_TIME_FORMAT)
                            ),
                            $scope->toArray()
                        );
                    }
                } catch (Exception $e) {
                    try {
                        $metadata = "";
                        if (!empty($result)) {
                            $metadata = $result->toJSON();
                        }
                        $this->jobRepository->markAsFailed($job->getEntityId(), $e->getMessage(), $metadata);
                    } catch (NoSuchEntityException $nfe) {
                        $this->logger->error($nfe, "Failed to mark the job as failed");
                    }
                    $this->logger->error($e, "Failed to process customer synchronization job");
                    continue;
                }
            }
        }

        $scopes = $this->scopeManager->getActiveScopes();
        if (empty($scopes)) {
            $this->logger->debug("No active scope found");
            return;
        }

        foreach ($scopes as $scope) {
            $found = false;
            foreach ($processedScopes as $processed) {
                if ($scope->equals($processed)) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                // This scope has already been processed manually
                // No need to re-process them again.
                $this->logger->debug("Ignoring scope. Already exported manually", $scope->toArray());
                continue;
            }

            try {
                if (!$this->config->isAutoSyncEnabled($scope->getType(), $scope->getId(), $category)) {
                    $this->logger->debug(
                        sprintf("Automatic %s sync is not enabled", $category),
                        $scope->toArray()
                    );
                    continue;
                }
                $this->logger->info("Checking customer export checkpoint", $scope->toArray());
                $checkpointCollection = $this->helper->createCheckpointCollection();
                $customerCheckpoint = $checkpointCollection->getCheckpoint($category, $scope);
                $total = $this->exportUpdatedCustomers($scope, $customerCheckpoint->getCheckedAt());
                $checkpointCollection->setCheckpoint($category, $now, $scope);
                if ($total > 0) {
                    $this->logger->info(
                        sprintf(
                            "%d customer(s) exported. Checkpoint's been updated to %s.",
                            $total,
                            $now->format(Config::DB_DATE_TIME_FORMAT)
                        ),
                        $scope->toArray()
                    );
                }
            } catch (Exception $e) {
                $this->logger->error($e, sprintf("Failed to export %s customers", $scope->toString()));
            }
        }
    }

    /**
     * @return ImportResponseInterface
     * @throws JsonException|OrttoException|NoSuchEntityException|LocalizedException|Exception
     */
    private function exportAllCustomers(Scope $scope, int $jobId)
    {
        $page = 1;
        $total = new ImportResponse();
        do {
            $job = $this->jobRepository->getJobById($jobId);
            if ($job->getStatus() !== Status::IN_PROGRESS) {
                throw new NoSuchEntityException(new Phrase("Customer synchronization job status changed (ID: $jobId)"));
            }

            $result = $this->customerRepository->getList($this->buildCustomerSearchCriteria($page, $scope));
            $customers = $result->getItems();
            $pageSize = 0;
            if (!empty($customers)) {
                $pageSize = count($customers);
                $importResult = $this->orttoClient->importContacts($scope, $customers);
                $total->incr($importResult);
                ++$page;
                $this->jobRepository->updateStats($jobId, $result->getTotalCount(), $pageSize, $total->toJSON());
            }
        } while ($pageSize === self::PAGE_SIZE);
        return $total;
    }

    /**
     * @param Scope $scope
     * @param string|null $checkpoint
     * @return int
     * @throws OrttoException
     * @throws JsonException|LocalizedException
     */
    private function exportUpdatedCustomers(Scope $scope, string $checkpoint = null): int
    {
        $page = 1;
        $total = 0;
        do {
            $searchCriteria = $this->buildCustomerSearchCriteria($page, $scope, $checkpoint);
            $result = $this->customerRepository->getList($searchCriteria);
            $customers = $result->getItems();
            $pageSize = 0;
            if (!empty($customers)) {
                $pageSize = count($customers);
                $importResult = $this->orttoClient->importContacts($scope, $customers);
                $total += $importResult->getCreatedTotal() + $importResult->getUpdatedTotal();
                ++$page;
            }
        } while ($pageSize === self::PAGE_SIZE);
        return $total;
    }

    private function buildCustomerSearchCriteria(int $page, Scope $scope, string $checkpoint = null)
    {
        if ($page < 1) {
            $page = 1;
        }
        $this->searchCriteriaBuilder
            ->setPageSize(self::PAGE_SIZE)
            ->setCurrentPage($page);

        if ($scope->getType() == ScopeInterface::SCOPE_WEBSITE) {
            $this->searchCriteriaBuilder->addFilter(CustomerInterface::WEBSITE_ID, $scope->getId());
        } else {
            $this->searchCriteriaBuilder->addFilter(CustomerInterface::STORE_ID, $scope->getId());
            $this->searchCriteriaBuilder->addFilter(CustomerInterface::WEBSITE_ID, $scope->getWebsiteId());
        }

        if (!empty($checkpoint)) {
            $this->searchCriteriaBuilder->addFilter(CustomerInterface::UPDATED_AT, $checkpoint, "gteq");
        }

        return $this->searchCriteriaBuilder->create();
    }
}
