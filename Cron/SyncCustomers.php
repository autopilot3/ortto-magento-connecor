<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Cron;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\CustomerReaderInterface;

use Autopilot\AP3Connector\Api\JobCategoryInterface as JobCategory;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Config;
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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
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
    private CustomerReaderInterface $customerReader;

    public function __construct(
        AutopilotLoggerInterface $logger,
        AutopilotClientInterface $autopilotClient,
        JobCollectionFactory $jobCollectionFactory,
        CheckpointCollectionFactory $checkpointCollectionFactory,
        ScopeManagerInterface $scopeManager,
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CustomerReaderInterface $customerReader
    ) {
        $this->logger = $logger;
        $this->autopilotClient = $autopilotClient;
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->scopeManager = $scopeManager;
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerReader = $customerReader;
        $this->checkpointCollectionFactory = $checkpointCollectionFactory;
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

            try {
                $this->logger->debug("Checking customer export checkpoint", $scope->toArray());
                $checkpoint = $checkpointCollection->getCheckpoint(JobCategory::CUSTOMER, $scope);
                $total = $this->exportUpdatedCustomers($scope, $checkpoint->getCheckedAt());
                $checkpoint = $checkpointCollection->setCheckpoint(JobCategory::CUSTOMER, $scope);
                $this->logger->debug(
                    sprintf(
                        "%d customer(s) exported. Checkpoint has been updated to %s",
                        $total,
                        $checkpoint->getCheckedAt()->format(Config::DATE_TIME_FORMAT)
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
     * @throws JsonException|AutopilotException|NoSuchEntityException
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

            $result = $this->customerReader->getScopeCustomers($scope, $page);
            $customers = $result->getCustomers();
            if (!empty($customers)) {
                $importResult = $this->autopilotClient->importContacts($scope, $customers);
                $total->incr($importResult);
                $page = $result->getCurrentPage() + 1;
                $jobCollection->updateStats($jobId, $result->getTotal(), count($customers), $total->toJSON());
            }
        } while ($result->hasMore());
        return $total;
    }

    /**
     * @param Scope $scope
     * @param ?DateTime $lastCheckedAt
     * @return int
     * @throws AutopilotException
     * @throws JsonException
     */
    private function exportUpdatedCustomers(Scope $scope, ?DateTime $lastCheckedAt): int
    {
        $page = 1;
        $total = 0;
        do {
            $result = $this->customerReader->getScopeCustomers($scope, $page, $lastCheckedAt);
            $customers = $result->getCustomers();
            if (!empty($customers)) {
                $total += count($customers);
                $this->autopilotClient->importContacts($scope, $customers);
                $page = $result->getCurrentPage() + 1;
            }
        } while ($result->hasMore());
        return $total;
    }
}
