<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Cron;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\ConfigurationReaderInterface;
use Autopilot\AP3Connector\Api\ImportResponseInterface;
use Autopilot\AP3Connector\Api\SyncCategoryInterface as SyncCategory;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Helper\To;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\AutopilotException;
use Autopilot\AP3Connector\Model\ImportResponse;
use Autopilot\AP3Connector\Model\Scope;
use Exception;
use Autopilot\AP3Connector\Api\JobStatusInterface as Status;
use JsonException;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Sales\Api\Data\OrderInterface;

class SyncProducts
{
    private const PAGE_SIZE = 100;

    private AutopilotLoggerInterface $logger;
    private AutopilotClientInterface $autopilotClient;
    private ScopeManagerInterface $scopeManager;
    private SortOrderBuilder $sortOrderBuilder;
    private ConfigurationReaderInterface $config;
    private Data $helper;
    private CollectionFactory $productCollectionFactory;
    private Visibility $productVisibility;

    public function __construct(
        AutopilotLoggerInterface $logger,
        AutopilotClientInterface $autopilotClient,
        ScopeManagerInterface $scopeManager,
        SortOrderBuilder $sortOrderBuilder,
        ConfigurationReaderInterface $config,
        CollectionFactory $productCollectionFactory,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->autopilotClient = $autopilotClient;
        $this->scopeManager = $scopeManager;
        $this->helper = $helper;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->config = $config;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Sync products with Autopilot
     *
     * @return void
     */
    public function execute(): void
    {
        $category = SyncCategory::PRODUCT;
        $this->logger->debug("Running product synchronization CRON job");
        /**
         * @var ScopeConfigInterface[]
         */
        $processedScopes = [];

        $now = $this->helper->nowUTC();

        try {
            $jobCollection = $this->helper->createJobCollection();
        } catch (Exception $e) {
            $this->logger->error($e);
            return;
        }

        $jobs = $jobCollection->getQueuedJobs($category);
        if (empty($jobs)) {
            $this->logger->debug("No product sync job was queued");
        } else {
            foreach ($jobs as $job) {
                $jobId = $job->getId();
                $this->logger->info(sprintf('Processing product synchronization job ID %s', $jobId));
                try {
                    $scope = $this->scopeManager->initialiseScope($job->getScopeType(), $job->getScopeId());
                    if (!$scope->isExplicitlyConnected()) {
                        $this->logger->warn("Job scope is not connected to Autopilot", $scope->toArray());
                        $jobCollection->markAsFailed($jobId, "Not connected to Autopilot");
                        continue;
                    }
                    $jobCollection->markAsInProgress($jobId);
                    $result = $this->exportAllProducts($scope, $jobId);
                    $processedScopes[] = $scope;
                    $jobCollection->markAsDone($jobId, $result->toJSON());
                    $this->helper->createCheckpointCollection()->setCheckpoint($category, $now, $scope);
                    $total = $result->getCreatedTotal() + $result->getUpdatedTotal();
                    if ($total > 0) {
                        $msg = sprintf(
                            "%d products have been manually exported. Checkpoint's been updated to %s.",
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
                        $jobCollection->markAsFailed($job->getId(), $e->getMessage(), $metadata);
                    } catch (NoSuchEntityException $nfe) {
                        $this->logger->error($nfe, "Failed to mark the job as failed");
                    }
                    $this->logger->error($e, "Failed to process product synchronization job");
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
                $this->logger->info("Checking product export checkpoint", $scope->toArray());
                $checkpointCollection = $this->helper->createCheckpointCollection();
                $productCheckpoint = $checkpointCollection->getCheckpoint($category, $scope);
                $result = $this->exportProducts($scope, null, null, $productCheckpoint->getCheckedAt());
                $checkpointCollection->setCheckpoint($category, $now, $scope);
                $total = $result->getCreatedTotal() + $result->getUpdatedTotal();
                if ($total > 0) {
                    $this->logger->info(
                        sprintf(
                            "%d products have been manually exported. Checkpoint's been updated to %s.",
                            $total,
                            $now->format(Config::DATE_TIME_FORMAT)
                        ),
                        $scope->toArray()
                    );
                }
            } catch (Exception $e) {
                $this->logger->error($e, sprintf("Failed to export %s products", $scope->toString()));
            }
        }
    }

    /**
     * @return ImportResponseInterface
     * @throws Exception|JsonException|AutopilotException|NoSuchEntityException|LocalizedException
     */
    private function exportAllProducts(Scope $scope, int $jobId)
    {
        $jobCollection = $this->helper->createJobCollection();
        $jobValidationCallback = function () use ($jobCollection, $jobId) {
            try {
                $job = $jobCollection->getJobById($jobId);
                $valid = $job->getStatus() === Status::IN_PROGRESS;
                if (!$valid) {
                    $this->logger->warn("Order synchronization job (ID: $jobId) has been changed.");
                }
                return $valid;
            } catch (Exception $e) {
                $this->logger->error($e, "Failed to check product synchronization job status (ID: $jobId)");
            }
            return false;
        };

        $stateUpdateCallback = function (int $total, int $processed, string $metadata) use ($jobCollection, $jobId) {
            $jobCollection->updateStats($jobId, $total, $processed, $metadata);
        };

        return $this->exportProducts($scope, $jobValidationCallback, $stateUpdateCallback);
    }

    /**
     * @param Scope $scope
     * @param null $validate
     * @param null $updateState
     * @param string|null $checkpoint
     * @return ImportResponseInterface
     * @throws AutopilotException|InvalidTransitionException|JsonException|LocalizedException
     */
    private function exportProducts(Scope $scope, $validate = null, $updateState = null, string $checkpoint = null)
    {
        $page = 1;
        $response = new ImportResponse();
        do {
            if ($validate !== null && !$validate()) {
                return $response;
            }
            $result = $this->getProducts($page, $scope, $checkpoint);
            /** @var ProductInterface[] $products
             * @var int $total
             */
            $products = $result['products'];
            $total = $result['total'];

            if ($total == 0 || empty($products)) {
                return $response;
            }
            $pageSize = count($products);
            $page++;
            $importResult = $this->autopilotClient->importProducts($scope, $products);
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
    private function getProducts(
        int $page,
        ConfigScopeInterface $scope,
        string $checkpoint = null
    ) {
        $collection = $this->productCollectionFactory->create()
            ->setCurPage($page)
            ->addAttributeToSelect('*')
            ->setPageSize(self::PAGE_SIZE)
            ->addWebsiteFilter($scope->getWebsiteId());

        if (!empty($checkpoint)) {
            $collection->addFieldToFilter(ProductInterface::UPDATED_AT, ['gteq' => $checkpoint]);
        }
        return [
            'total' => To::int($collection->getSize()),
            'products' => $collection->getItems(),
        ];
    }
}
