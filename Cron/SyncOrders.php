<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Cron;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\ConfigScopeInterface;
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

    private AutopilotLoggerInterface $logger;
    private AutopilotClientInterface $autopilotClient;
    private ScopeManagerInterface $scopeManager;
    private Data $helper;

    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private OrderRepositoryInterface $orderRepository;

    private SortOrderBuilder $sortOrderBuilder;

    public function __construct(
        AutopilotLoggerInterface $logger,
        AutopilotClientInterface $autopilotClient,
        ScopeManagerInterface $scopeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        SortOrderBuilder $sortOrderBuilder,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->autopilotClient = $autopilotClient;
        $this->scopeManager = $scopeManager;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Sync orders with Autopilot
     *
     * @return void
     */
    public function execute(): void
    {
        $this->logger->debug("Running order synchronization CRON job");
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

        $jobs = $jobCollection->getQueuedJobs(JobCategory::ORDER);
        if (empty($jobs)) {
            $this->logger->debug("No order sync job was queued");
        } else {
            foreach ($jobs as $job) {
                $jobId = $job->getId();
                $this->logger->info(sprintf('Processing order synchronization job ID %s', $jobId));
                try {
                    $scope = $this->scopeManager->initialiseScope($job->getScopeType(), $job->getScopeId());
                    if (!$scope->isConnected()) {
                        $this->logger->warn("Job scope is not connected to Autopilot", $scope->toArray());
                        $jobCollection->markAsFailed($jobId, "Not connected to Autopilot");
                        continue;
                    }
                    $jobCollection->markAsInProgress($jobId);
                    $result = $this->exportAllOrders($scope, $jobId);
                    $processedScopes[] = $scope;
                    $jobCollection->markAsDone($jobId, $result->toJSON());
                    $this->helper->createCheckpointCollection()->setCheckpoint(JobCategory::ORDER, $now, $scope);
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
                        $jobCollection->markAsFailed($job->getId(), $e->getMessage(), $metadata);
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
                $this->logger->info("Checking order export checkpoint", $scope->toArray());
                $checkpointCollection = $this->helper->createCheckpointCollection();
                $orderCheckpoint = $checkpointCollection->getCheckpoint(JobCategory::ORDER, $scope);
                $result = $this->exportOrders($scope, null, null, $orderCheckpoint->getCheckedAt());
                $checkpointCollection->setCheckpoint(JobCategory::ORDER, $now, $scope);
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
     * @throws Exception|JsonException|AutopilotException|NoSuchEntityException|LocalizedException
     */
    private function exportAllOrders(Scope $scope, int $jobId)
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
                $this->logger->error($e, "Failed to check order synchronization job status (ID: $jobId)");
            }
            return false;
        };

        $stateUpdateCallback = function (int $total, int $processed, string $metadata) use ($jobCollection, $jobId) {
            $jobCollection->updateStats($jobId, $total, $processed, $metadata);
        };

        return $this->exportOrders($scope, $jobValidationCallback, $stateUpdateCallback);
    }

    /**
     * @param Scope $scope
     * @param null $validate
     * @param null $updateState
     * @param string|null $checkpoint
     * @return ImportResponseInterface
     * @throws AutopilotException|InvalidTransitionException|JsonException|LocalizedException
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
                $importResult = $this->autopilotClient->importOrders($scope, $orders);
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
