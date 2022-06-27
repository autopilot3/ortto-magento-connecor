<?php
declare(strict_types=1);

namespace Ortto\Connector\Cron;

use Magento\ProductAlert\Model\Stock;
use Magento\Store\Model\ScopeInterface;
use Ortto\Connector\Api\OrttoClientInterface;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\ConfigurationReaderInterface;
use Ortto\Connector\Api\ImportResponseInterface;
use Ortto\Connector\Api\SyncCategoryInterface as SyncCategory;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Api\SyncJobRepositoryInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Model\OrttoException;
use Ortto\Connector\Model\ImportResponse;
use Ortto\Connector\Model\Scope;
use Exception;
use Ortto\Connector\Api\JobStatusInterface as Status;
use JsonException;
use Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;

class SyncStockAlerts
{
    private const PAGE_SIZE = 100;

    private OrttoLoggerInterface $logger;
    private OrttoClientInterface $orttoClient;
    private ScopeManagerInterface $scopeManager;
    private ConfigurationReaderInterface $config;
    private Data $helper;
    private CollectionFactory $stockAlertsCollectionFactory;
    private SyncJobRepositoryInterface $jobRepository;

    public function __construct(
        OrttoLoggerInterface $logger,
        OrttoClientInterface $orttoClient,
        ScopeManagerInterface $scopeManager,
        ConfigurationReaderInterface $config,
        CollectionFactory $stockAlertsCollectionFactory,
        Data $helper,
        SyncJobRepositoryInterface $jobRepository
    ) {
        $this->logger = $logger;
        $this->orttoClient = $orttoClient;
        $this->scopeManager = $scopeManager;
        $this->helper = $helper;
        $this->config = $config;
        $this->stockAlertsCollectionFactory = $stockAlertsCollectionFactory;
        $this->jobRepository = $jobRepository;
    }

    /**
     * Sync products with Ortto
     *
     * @return void
     */
    public function execute(): void
    {
        $category = SyncCategory::STOCK_ALERT;
        $this->logger->debug("Running stock alerts synchronization CRON job");
        /**
         * @var ScopeConfigInterface[]
         */
        $processedScopes = [];

        $now = $this->helper->nowUTC();

        $jobs = $this->jobRepository->getQueuedJobs($category);
        if (empty($jobs)) {
            $this->logger->debug("No stock alerts sync job was queued");
        } else {
            foreach ($jobs as $job) {
                $jobId = $job->getEntityId();
                $this->logger->info(sprintf('Processing stock alerts synchronization job ID %s', $jobId));
                try {
                    $scope = $this->scopeManager->initialiseScope($job->getScopeType(), $job->getScopeId());
                    if (!$scope->isExplicitlyConnected()) {
                        $this->logger->warn("Job scope is not connected to Ortto", $scope->toArray());
                        $this->jobRepository->markAsFailed($jobId, "Not connected to Ortto");
                        continue;
                    }
                    $this->jobRepository->markAsInProgress($jobId);
                    $result = $this->exportAllStockAlerts($scope, $jobId);
                    $processedScopes[] = $scope;
                    $this->jobRepository->markAsDone($jobId, $result->toJSON());
                    $this->helper->createCheckpointCollection()->setCheckpoint($category, $now, $scope);
                    $total = $result->getCreatedTotal() + $result->getUpdatedTotal();
                    if ($total > 0) {
                        $msg = sprintf(
                            "%d stock alerts have been manually exported. Checkpoint's been updated to %s.",
                            $total,
                            $now->format(Config::DATE_TIME_FORMAT)
                        );
                        $this->logger->info($msg, $scope->toArray());
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
                    $this->logger->error($e, "Failed to process stock alerts synchronization job");
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
                $this->logger->info("Checking stock alerts export checkpoint", $scope->toArray());
                $checkpointCollection = $this->helper->createCheckpointCollection();
                $productCheckpoint = $checkpointCollection->getCheckpoint($category, $scope);
                $result = $this->exportStockAlerts($scope, null, null, $productCheckpoint->getCheckedAt());
                $checkpointCollection->setCheckpoint($category, $now, $scope);
                $total = $result->getCreatedTotal() + $result->getUpdatedTotal();
                if ($total > 0) {
                    $this->logger->info(
                        sprintf(
                            "%d stock alerts have been manually exported. Checkpoint's been updated to %s.",
                            $total,
                            $now->format(Config::DATE_TIME_FORMAT)
                        ),
                        $scope->toArray()
                    );
                }
            } catch (Exception $e) {
                $this->logger->error($e, sprintf("Failed to export %s stock alerts", $scope->toString()));
            }
        }
    }

    /**
     * @return ImportResponseInterface
     * @throws Exception|JsonException|OrttoException|NoSuchEntityException|LocalizedException
     */
    private function exportAllStockAlerts(Scope $scope, int $jobId)
    {
        $jobValidationCallback = function () use ($jobId) {
            try {
                $job = $this->jobRepository->getJobById($jobId);
                $valid = $job->getStatus() === Status::IN_PROGRESS;
                if (!$valid) {
                    $this->logger->warn("Stock alert synchronization job (ID: $jobId) has been changed.");
                }
                return $valid;
            } catch (Exception $e) {
                $this->logger->error($e, "Failed to check stock alerts synchronization job status (ID: $jobId)");
            }
            return false;
        };

        $stateUpdateCallback = function (int $total, int $processed, string $metadata) use ($jobId) {
            $this->jobRepository->updateStats($jobId, $total, $processed, $metadata);
        };

        return $this->exportStockAlerts($scope, $jobValidationCallback, $stateUpdateCallback);
    }

    /**
     * @param Scope $scope
     * @param null $validate
     * @param null $updateState
     * @param string|null $checkpoint
     * @return ImportResponseInterface
     * @throws OrttoException|InvalidTransitionException|JsonException|LocalizedException
     */
    private function exportStockAlerts(Scope $scope, $validate = null, $updateState = null, string $checkpoint = null)
    {
        $page = 1;
        $response = new ImportResponse();
        do {
            if ($validate !== null && !$validate()) {
                return $response;
            }
            $result = $this->getStockAlerts($page, $scope, $checkpoint);
            /** @var Stock[] $stockAlerts
             * @var int $total
             */
            $stockAlerts = $result['alerts'];
            $total = $result['total'];

            if ($total == 0 || empty($stockAlerts)) {
                return $response;
            }
            $pageSize = count($stockAlerts);
            $page++;
            $importResult = $this->orttoClient->importRestockSubscriptions($scope, $stockAlerts);
            $response->incr($importResult);
            if ($updateState !== null) {
                $updateState($total, $pageSize, $response->toJSON());
            }
        } while ($pageSize == self::PAGE_SIZE);

        return $response;
    }

    /**
     * @param int $page
     * @param ConfigScopeInterface $scope
     * @param string|null $checkpoint
     * @return array
     */
    private function getStockAlerts(
        int $page,
        ConfigScopeInterface $scope,
        string $checkpoint = null
    ) {
        $collection = $this->stockAlertsCollectionFactory->create()
            ->setCurPage($page)
            ->addFieldToSelect('*')
            ->setPageSize(self::PAGE_SIZE)
            ->addWebsiteFilter($scope->getWebsiteId());

        if ($scope->getType() == ScopeInterface::SCOPE_STORE) {
            $collection->addFieldToFilter('store_id', ['eq' => $scope->getId()]);
        }

        if (!empty($checkpoint)) {
            $collection->addFieldToFilter('add_date', ['gteq' => $checkpoint]);
        }
        return [
            'total' => To::int($collection->getSize()),
            'alerts' => $collection->getItems(),
        ];
    }
}
