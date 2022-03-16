<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Cron;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\ImportResponseInterface;
use Autopilot\AP3Connector\Api\JobCategoryInterface as JobCategory;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\AutopilotException;
use Autopilot\AP3Connector\Model\ImportResponse;
use Autopilot\AP3Connector\Model\Scope;
use Exception;
use Autopilot\AP3Connector\Api\JobStatusInterface as Status;
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
    private AutopilotLoggerInterface $logger;
    private AutopilotClientInterface $autopilotClient;
    private ScopeManagerInterface $scopeManager;
    private Data $helper;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private CustomerRepositoryInterface $customerRepository;

    const PAGE_SIZE = 100;

    public function __construct(
        AutopilotLoggerInterface $logger,
        AutopilotClientInterface $autopilotClient,
        ScopeManagerInterface $scopeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $customerRepository,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->autopilotClient = $autopilotClient;
        $this->scopeManager = $scopeManager;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Sync customers with Autopilot
     *
     * @return void
     */
    public function execute(): void
    {
        $this->logger->debug("Running customer synchronization CRON job");
        /**
         * @var ScopeConfigInterface[]
         */
        $processedScopes = [];

        $now = $this->helper->now();

        try {
            $jobCollection = $this->helper->createJobCollection();
        } catch (Exception $e) {
            $this->logger->error($e);
            return;
        }

        $jobs = $jobCollection->getQueuedJobs(JobCategory::CUSTOMER);
        if (empty($jobs)) {
            $this->logger->debug("No customer sync job was queued");
        } else {
            foreach ($jobs as $job) {
                $jobId = $job->getId();
                $this->logger->info(sprintf('Processing customer synchronization job ID %s', $jobId));
                try {
                    $scope = $this->scopeManager->initialiseScope($job->getScopeType(), $job->getScopeId());
                    if (!$scope->isConnected()) {
                        $this->logger->warn("Job scope is not connected to Autopilot", $scope->toArray());
                        $jobCollection->markAsFailed($jobId, "Not connected to Autopilot");
                        continue;
                    }
                    $jobCollection->markAsInProgress($jobId);
                    $result = $this->exportAllCustomers($scope, $jobId);
                    $processedScopes[] = $scope;
                    $jobCollection->markAsDone($jobId, $result->toJSON());
                    $this->helper->createCheckpointCollection()->setCheckpoint(JobCategory::CUSTOMER, $now, $scope);
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
                        $jobCollection->markAsFailed($job->getId(), $e->getMessage(), $metadata);
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
                $this->logger->info("Checking customer export checkpoint", $scope->toArray());
                $checkpointCollection = $this->helper->createCheckpointCollection();
                $customerCheckpoint = $checkpointCollection->getCheckpoint(JobCategory::CUSTOMER, $scope);
                $total = $this->exportUpdatedCustomers($scope, $customerCheckpoint->getCheckedAt());
                $checkpointCollection->setCheckpoint(JobCategory::CUSTOMER, $now, $scope);
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
     * @throws JsonException|AutopilotException|NoSuchEntityException|LocalizedException|Exception
     */
    private function exportAllCustomers(Scope $scope, int $jobId)
    {
        $page = 1;
        $total = new ImportResponse();
        do {
            $jobCollection = $this->helper->createJobCollection();
            $job = $jobCollection->getJobById($jobId);
            if ($job->getStatus() !== Status::IN_PROGRESS) {
                throw new NoSuchEntityException(new Phrase("Customer synchronization job status changed (ID: $jobId)"));
            }

            $result = $this->customerRepository->getList($this->buildCustomerSearchCriteria($page, $scope));
            $customers = $result->getItems();
            $pageSize = 0;
            if (!empty($customers)) {
                $pageSize = count($customers);
                $importResult = $this->autopilotClient->importContacts($scope, $customers);
                $total->incr($importResult);
                ++$page;
                $jobCollection->updateStats($jobId, $result->getTotalCount(), $pageSize, $total->toJSON());
            }
        } while ($pageSize === self::PAGE_SIZE);
        return $total;
    }

    /**
     * @param Scope $scope
     * @param string|null $checkpoint
     * @return int
     * @throws AutopilotException
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
                $importResult = $this->autopilotClient->importContacts($scope, $customers);
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
