<?php


namespace Autopilot\AP3Connector\Cron;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\CustomerReaderInterface;

use Autopilot\AP3Connector\Api\JobCategoryInterface as JobCategory;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\AutopilotException;
use Autopilot\AP3Connector\Model\ImportContactResponse;
use Autopilot\AP3Connector\Model\ResourceModel\SyncJob\Collection as JobCollection;
use AutoPilot\AP3Connector\Model\ResourceModel\SyncJob\CollectionFactory as JobCollectionFactory;
use Autopilot\AP3Connector\Model\Scope;
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
    private Data $helper;
    private AutopilotClientInterface $autopilotClient;
    private JobCollectionFactory $jobCollectionFactory;
    private ScopeManagerInterface $scopeManager;
    private EncryptorInterface $encryptor;
    private ScopeConfigInterface $scopeConfig;
    private StoreManagerInterface $storeManager;
    private CustomerReaderInterface $customerReader;

    public function __construct(
        AutopilotLoggerInterface $logger,
        Data $helper,
        AutopilotClientInterface $autopilotClient,
        JobCollectionFactory $jobCollectionFactory,
        ScopeManagerInterface $scopeManager,
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CustomerReaderInterface $customerReader
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->autopilotClient = $autopilotClient;
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->scopeManager = $scopeManager;
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerReader = $customerReader;
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
                return;
            }
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
        } else {
            $this->logger->error(new Exception("Invalid job collection type"));
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
                continue;
            }
            $this->exportUpdatedCustomers($scope);
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
            $importResult = $this->autopilotClient->importContacts($scope, $customers);
            $total->incr($importResult);
            $page = $result->getCurrentPage() + 1;
            $jobCollection->updateStats($jobId, $result->getTotal(), count($customers), $total->toJSON());
        } while ($result->hasMore());
        return $total;
    }

    private function exportUpdatedCustomers(Scope $scope)
    {
        //TODO: Implement me
    }
}
