<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Cron;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\JobCategoryInterface as JobCategory;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\AutopilotException;
use Autopilot\AP3Connector\Model\ImportContactResponse;
use Autopilot\AP3Connector\Model\ResourceModel\SyncJob\Collection as JobCollection;
use AutoPilot\AP3Connector\Model\ResourceModel\SyncJob\CollectionFactory as JobCollectionFactory;
use Autopilot\AP3Connector\Model\ResourceModel\CronCheckpoint\Collection as CheckpointCollection;
use AutoPilot\AP3Connector\Model\ResourceModel\CronCheckpoint\CollectionFactory as CheckpointCollectionFactory;
use Autopilot\AP3Connector\Model\Scope;
use DateTime;
use Exception;
use Autopilot\AP3Connector\Api\JobStatusInterface as Status;
use JsonException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class SyncCustomers
{
    private AutopilotLoggerInterface $logger;
    private AutopilotClientInterface $autopilotClient;
    private JobCollectionFactory $jobCollectionFactory;
    private CheckpointCollectionFactory $checkpointCollectionFactory;
    private ScopeManagerInterface $scopeManager;
    private EncryptorInterface $encryptor;
    private ScopeConfigInterface $scopeConfig;
    private StoreManagerInterface $storeManager;
    private Data $helper;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private CustomerRepositoryInterface $customerRepository;

    const PAGE_SIZE = 100;


    public function __construct(
        AutopilotLoggerInterface $logger,
        AutopilotClientInterface $autopilotClient,
        JobCollectionFactory $jobCollectionFactory,
        CheckpointCollectionFactory $checkpointCollectionFactory,
        ScopeManagerInterface $scopeManager,
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $customerRepository,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->autopilotClient = $autopilotClient;
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->scopeManager = $scopeManager;
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->checkpointCollectionFactory = $checkpointCollectionFactory;
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
        $jobCollection = $this->jobCollectionFactory->create();
        if ($jobCollection instanceof JobCollection) {
            $jobs = $jobCollection->getQueuedJobs(JobCategory::CUSTOMER);
            if (empty($jobs)) {
                $this->logger->debug("No sync jobs were queued");
            } else {
                foreach ($jobs as $job) {
                    $jobId = $job->getId();
                    $this->logger->debug(sprintf('Processing customer synchronization job ID %s', $jobId));
                    $scope = new Scope($this->encryptor, $this->scopeConfig, $this->storeManager);
                    try {
                        $scope->load($job->getScopeType(), $job->getScopeId());
                        if (!$scope->isConnected()) {
                            $this->logger->warn("Job scope is not connected to Autopilot", $scope->toArray());
                            $jobCollection->markAsFailed($jobId, "Not connected to Autopilot");
                            continue;
                        }
                        $jobCollection->markAsInProgress($jobId);
                        $result = $this->exportAllCustomers($scope, $jobCollection, $jobId);
                        $processedScopes[] = $scope;
                        $jobCollection->markAsDone($jobId, $result->toJSON());
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
        } else {
            $this->logger->error(new Exception("Invalid job collection type"));
        }

        $checkpointCollection = $this->checkpointCollectionFactory->create();
        if (!($checkpointCollection instanceof CheckpointCollection)) {
            $this->logger->error(new Exception("Invalid checkpoint collection type"));
            return;
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

            $now = $this->helper->now();

            try {
                $this->logger->debug("Checking customer export checkpoint", $scope->toArray());
                $customerCheckpoint = $checkpointCollection->getCheckpoint(JobCategory::CUSTOMER, $scope);
                $total = $this->exportUpdatedCustomers($scope, $customerCheckpoint->getCheckedAt());
                $checkpointCollection->setCheckpoint(JobCategory::CUSTOMER, $now, $scope);
                $this->logger->debug(
                    sprintf(
                        "%d customer(s) exported. Checkpoints updated to %s.",
                        $total,
                        $now->format(Config::DATE_TIME_FORMAT)
                    ),
                    $scope->toArray()
                );
            } catch (Exception $e) {
                $this->logger->error($e, sprintf("Failed to export %s customers", $scope->toString()));
            }
        }
    }

    /**
     * @return ImportContactResponse
     * @throws JsonException|AutopilotException|NoSuchEntityException|LocalizedException
     */
    private function exportAllCustomers(Scope $scope, JobCollection $jobCollection, int $jobId)
    {
        $page = 1;
        $total = new ImportContactResponse();
        do {
            $job = $jobCollection->getJobById($jobId);
            if ($job->getStatus() !== Status::IN_PROGRESS) {
                throw new NoSuchEntityException(new Phrase("Job status changed"));
            }

            $result = $this->customerRepository->getList($this->buildCustomerSearchCriteria($page, $scope));
            $customers = $result->getItems();
            $pageSize = 0;
            if (!empty($customers)) {
                $pageSize = count($customers);
                $importResult = $this->autopilotClient->importContacts($scope, $customers);
                $total->incr($importResult);
                $page += 1;
                $contacts = $importResult->getContacts();
                if (!empty($contacts)) {
                    foreach ($customers as $customer) {
                        $contactId = $contacts[$customer->getId()];
                        if (isset($contactId)) {
                            $attributes = $customer->getExtensionAttributes();
                            $attributes->setAutopilotContactId($contactId);
                            $customer->setExtensionAttributes($attributes);
                            $this->customerRepository->save($customer);
                        }
                    }
                }
                $jobCollection->updateStats($jobId, $result->getTotalCount(), $pageSize, $total->toJSON());
            }
        } while ($pageSize === self::PAGE_SIZE);
        return $total;
    }

    /**
     * @param Scope $scope
     * @param ?DateTime $checkpoint
     * @return int
     * @throws AutopilotException
     * @throws JsonException|LocalizedException
     */
    private function exportUpdatedCustomers(
        Scope $scope,
        ?DateTime $checkpoint = null
    ): int {
        $page = 1;
        $total = 0;
        do {
            $searchCriteria = $this->buildCustomerSearchCriteria($page, $scope, $checkpoint);
            $result = $this->customerRepository->getList($searchCriteria);
            $customers = $result->getItems();
            $pageSize = 0;
            if (!empty($customers)) {
                $pageSize = count($customers);
                $total += $pageSize;
                $this->autopilotClient->importContacts($scope, $customers);
                $page += 1;
            }
        } while ($pageSize === self::PAGE_SIZE);
        return $total;
    }


    private function buildCustomerSearchCriteria(int $page, Scope $scope, ?DateTime $checkpoint = null)
    {
        if ($page < 1) {
            $page = 1;
        }
        $this->searchCriteriaBuilder
            ->setPageSize(self::PAGE_SIZE)
            ->setCurrentPage($page);

        if ($scope->getType() === ScopeInterface::SCOPE_WEBSITE) {
            $this->searchCriteriaBuilder->addFilter(CustomerInterface::WEBSITE_ID, $scope->getId());
        } else {
            $this->searchCriteriaBuilder->addFilter(CustomerInterface::STORE_ID, $scope->getId());
            $this->searchCriteriaBuilder->addFilter(CustomerInterface::WEBSITE_ID, $scope->getWebsiteId());
        }

        if (!empty($checkpoint)) {
            $this->searchCriteriaBuilder->addFilter(CustomerInterface::UPDATED_AT, $checkpoint, "gt");
        }

        return $this->searchCriteriaBuilder->create();
    }
}
