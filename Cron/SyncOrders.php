<?php
declare(strict_types=1);

namespace Ortto\Connector\Cron;

use Ortto\Connector\Api\OrttoClientInterface;
use Ortto\Connector\Api\ConfigScopeInterface;
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
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class SyncOrders
{
    private const PAGE_SIZE = 100;

    private OrttoLoggerInterface $logger;
    private OrttoClientInterface $orttoClient;
    private ScopeManagerInterface $scopeManager;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private OrderRepositoryInterface $orderRepository;
    private SortOrderBuilder $sortOrderBuilder;
    private ConfigurationReaderInterface $config;
    private Data $helper;
    private SyncJobRepositoryInterface $jobRepository;

    public function __construct(
        OrttoLoggerInterface $logger,
        OrttoClientInterface $orttoClient,
        ScopeManagerInterface $scopeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        SortOrderBuilder $sortOrderBuilder,
        ConfigurationReaderInterface $config,
        Data $helper,
        SyncJobRepositoryInterface $jobRepository
    ) {
        $this->logger = $logger;
        $this->orttoClient = $orttoClient;
        $this->scopeManager = $scopeManager;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->config = $config;
        $this->jobRepository = $jobRepository;
    }

    /**
     * Sync orders with Ortto
     *
     * @return void
     */
    public function execute(): void
    {
        $category = SyncCategory::ORDER;
        $this->logger->debug("Running order synchronization CRON job");
        /**
         * @var ScopeConfigInterface[]
         */
        $processedScopes = [];

        $now = $this->helper->nowUTC();

        $jobs = $this->jobRepository->getQueuedJobs($category);
        if (empty($jobs)) {
            $this->logger->debug("No order sync job was queued");
        } else {
            foreach ($jobs as $job) {
                $jobId = $job->getEntityId();
                $this->logger->info(sprintf('Processing order synchronization job ID %s', $jobId));
                try {
                    $scope = $this->scopeManager->initialiseScope($job->getScopeType(), $job->getScopeId());
                    if (!$scope->isExplicitlyConnected()) {
                        $this->logger->warn("Job scope is not connected to Ortto", $scope->toArray());
                        $this->jobRepository->markAsFailed($jobId, "Not connected to Ortto");
                        continue;
                    }
                    $this->jobRepository->markAsInProgress($jobId);
                    $result = $this->exportAllOrders($scope, $jobId);
                    $processedScopes[] = $scope;
                    $this->jobRepository->markAsDone($jobId, $result->toJSON());
                    $this->helper->createCheckpointCollection()->setCheckpoint($category, $now, $scope);
                    $total = $result->getCreatedTotal() + $result->getUpdatedTotal();
                    if ($total > 0) {
                        $msg = sprintf(
                            "%d customer(s) with orders have been manually exported. Checkpoint's been updated to %s.",
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
                    $this->logger->error($e, "Failed to process order synchronization job");
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
                $this->logger->info("Checking order export checkpoint", $scope->toArray());
                $checkpointCollection = $this->helper->createCheckpointCollection();
                $orderCheckpoint = $checkpointCollection->getCheckpoint($category, $scope);
                $result = $this->exportOrders($scope, null, null, $orderCheckpoint->getCheckedAt());
                $checkpointCollection->setCheckpoint($category, $now, $scope);
                $total = $result->getCreatedTotal() + $result->getUpdatedTotal();
                if ($total > 0) {
                    $this->logger->info(
                        sprintf(
                            "%d customer(s) with orders have been manually exported. Checkpoint's been updated to %s.",
                            $total,
                            $now->format(Config::DATE_TIME_FORMAT)
                        ),
                        $scope->toArray()
                    );
                }
            } catch (Exception $e) {
                $this->logger->error($e, sprintf("Failed to export %s orders", $scope->toString()));
            }
        }
    }

    /**
     * @return ImportResponseInterface
     * @throws Exception|JsonException|OrttoException|NoSuchEntityException|LocalizedException
     */
    private function exportAllOrders(Scope $scope, int $jobId)
    {
        $jobValidationCallback = function () use ($jobId) {
            try {
                $job = $this->jobRepository->getJobById($jobId);
                $valid = $job->getStatus() === Status::IN_PROGRESS;
                if (!$valid) {
                    $this->logger->warn("Order synchronization job (ID: $jobId) has been changed.");
                }
                return $valid;
            } catch (Exception $e) {
                $this->logger->error($e, "Failed to check order synchronization job status (ID: $jobId)");
            }
            return false;
        };

        $stateUpdateCallback = function (int $total, int $processed, string $metadata) use ($jobId) {
            $this->jobRepository->updateStats($jobId, $total, $processed, $metadata);
        };

        return $this->exportOrders($scope, $jobValidationCallback, $stateUpdateCallback);
    }

    /**
     * @param Scope $scope
     * @param null $validate
     * @param null $updateState
     * @param string|null $checkpoint
     * @return ImportResponseInterface
     * @throws OrttoException|InvalidTransitionException|JsonException|LocalizedException
     */
    private function exportOrders(Scope $scope, $validate = null, $updateState = null, string $checkpoint = null)
    {
        $page = 1;
        $total = new ImportResponse();
        do {
            if ($validate !== null && !$validate()) {
                return $total;
            }
            $result = $this->getOrders($page, $scope, $checkpoint);
            $pageSize = 0;
            if (!empty($result)) {
                $orders = $result->getItems();
                if (empty($orders)) {
                    return $total;
                }
                $pageSize = count($orders);
                $page++;
                $importResult = $this->orttoClient->importOrders($scope, $orders);
                $total->incr($importResult);
                if ($updateState !== null) {
                    $updateState($result->getTotalCount(), $pageSize, $total->toJSON());
                }
            }
        } while ($pageSize == self::PAGE_SIZE);

        return $total;
    }

    /**
     * @param int $page
     * @param ConfigScopeInterface $scope
     * @param string|null $checkpoint
     * @return OrderSearchResultInterface
     */
    private function getOrders(
        int $page,
        ConfigScopeInterface $scope,
        string $checkpoint = null
    ) {
        $this->searchCriteriaBuilder->setPageSize(self::PAGE_SIZE)
            ->setCurrentPage($page)
            ->addFilter(OrderInterface::STORE_ID, implode(',', $scope->getStoreIds()), 'in');

        if (!empty($checkpoint)) {
            $this->searchCriteriaBuilder->addFilter(OrderInterface::UPDATED_AT, $checkpoint, 'gteq');
        }
        $sortOrder = $this->sortOrderBuilder->setField(OrderInterface::CREATED_AT)->setDirection(SortOrder::SORT_ASC);
        $this->searchCriteriaBuilder->addSortOrder($sortOrder->create());
        return $this->orderRepository->getList($this->searchCriteriaBuilder->create());
    }
}
