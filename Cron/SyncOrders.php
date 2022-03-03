<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Cron;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\Data\CustomerOrderInterface;
use Autopilot\AP3Connector\Api\JobCategoryInterface as JobCategory;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\AutopilotException;
use Autopilot\AP3Connector\Model\CustomerOrder;
use Autopilot\AP3Connector\Model\ImportOrderResponse;
use AutoPilot\AP3Connector\Model\ResourceModel\CustomerAttributes\CollectionFactory as CustomerAttrCollectionFactory;
use Autopilot\AP3Connector\Model\ResourceModel\SyncJob\Collection as JobCollection;
use AutoPilot\AP3Connector\Model\ResourceModel\SyncJob\CollectionFactory as JobCollectionFactory;
use Autopilot\AP3Connector\Model\ResourceModel\CronCheckpoint\Collection as CheckpointCollection;
use AutoPilot\AP3Connector\Model\ResourceModel\CronCheckpoint\CollectionFactory as CheckpointCollectionFactory;
use Autopilot\AP3Connector\Model\Scope;
use DateTime;
use Exception;
use Autopilot\AP3Connector\Api\JobStatusInterface as Status;
use JsonException;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Autopilot\AP3Connector\Model\CustomerOrderFactory;

class SyncOrders
{
    private const CUSTOMER_PAGE_SIZE = 100;
    private const ORDER_PAGE_SIZE = 50;
    private const ITEMS_KEY = 'items';
    private const TOTAL_KEY = 'total';
    private const CUSTOMER_ID = 'entity_id';

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
    private OrderRepositoryInterface $orderRepository;

    private CustomerAttrCollectionFactory $attrCollectionFactory;
    private CustomerCollectionFactory $customerCollectionFactory;
    private CustomerOrderFactory $customerOrderFactory;
    private SortOrderBuilder $sortOrderBuilder;

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
        OrderRepositoryInterface $orderRepository,
        CustomerAttrCollectionFactory $attrCollectionFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        CustomerOrderFactory $customerOrderFactory,
        SortOrderBuilder $sortOrderBuilder,
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
        $this->orderRepository = $orderRepository;
        $this->attrCollectionFactory = $attrCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerOrderFactory = $customerOrderFactory;
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
        $checkpointCollection = $this->checkpointCollectionFactory->create();
        if (!($checkpointCollection instanceof CheckpointCollection)) {
            $this->logger->error(new Exception("Invalid checkpoint collection type"));
            return;
        }

        $now = $this->helper->now();

        $jobCollection = $this->jobCollectionFactory->create();

        if (!($jobCollection instanceof JobCollection)) {
            $this->logger->error(new Exception("Invalid job collection type"));
            return;
        }
        $jobs = $jobCollection->getQueuedJobs(JobCategory::ORDER);
        if (empty($jobs)) {
            $this->logger->debug("No order sync job was queued");
        } else {
            foreach ($jobs as $job) {
                $jobId = $job->getId();
                $this->logger->info(sprintf('Processing order synchronization job ID %s', $jobId));
                $scope = new Scope($this->encryptor, $this->scopeConfig, $this->storeManager);
                try {
                    $scope->load($job->getScopeType(), $job->getScopeId());
                    if (!$scope->isConnected()) {
                        $this->logger->warn("Job scope is not connected to Autopilot", $scope->toArray());
                        $jobCollection->markAsFailed($jobId, "Not connected to Autopilot");
                        continue;
                    }
                    $jobCollection->markAsInProgress($jobId);
                    $result = $this->exportAllOrders($scope, $jobCollection, $jobId);
                    $processedScopes[] = $scope;
                    $jobCollection->markAsDone($jobId, $result->toJSON());
                    $checkpointCollection->setCheckpoint(JobCategory::ORDER, $now, $scope);
                    $total = $result->getOrdersTotal();
                    if ($total > 0) {
                        $this->logger->info(
                            sprintf(
                                "%d order(s) have been manually exported. Checkpoint's been updated to %s.",
                                $total,
                                $now->format(Config::DATE_TIME_FORMAT)
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
                $orderCheckpoint = $checkpointCollection->getCheckpoint(JobCategory::ORDER, $scope);
                $result = $this->exportOrders($scope, null, null, $orderCheckpoint->getCheckedAt());
                $checkpointCollection->setCheckpoint(JobCategory::ORDER, $now, $scope);
                $total = $result->getOrdersTotal();
                if ($total > 0) {
                    $this->logger->info(
                        sprintf(
                            "%d order(s) have been manually exported. Checkpoint's been updated to %s.",
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
     * @return ImportOrderResponse
     * @throws Exception|JsonException|AutopilotException|NoSuchEntityException|LocalizedException
     */
    private function exportAllOrders(Scope $scope, JobCollection $jobCollection, int $jobId)
    {
        $jobValidationCallback = function () use ($jobCollection, $jobId) {
            try {
                $job = $jobCollection->getJobById($jobId);
                $valid = $job->getStatus() === Status::IN_PROGRESS;
                if ($valid) {
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
     * @param DateTime|null $checkpoint
     * @return ImportOrderResponse
     * @throws AutopilotException|InvalidTransitionException|JsonException|LocalizedException
     */
    private function exportOrders(Scope $scope, $validate = null, $updateState = null, ?DateTime $checkpoint = null)
    {
        $currentCustomerPage = 1;
        $total = new ImportOrderResponse();
        /** @var CustomerOrderInterface[] $toExport */
        $toExport = [];
        do {
            if ($validate !== null && !$validate()) {
                return $total;
            }
            $customersResult = $this->getCustomers($currentCustomerPage);
            /** @var int $customersTotal */
            $customersTotal = $customersResult[self::TOTAL_KEY];
            /** @var CustomerInterface[] $customers */
            $customers = $customersResult[self::ITEMS_KEY];
            $customerPageSize = 0;
            if (!empty($customers)) {
                $customerPageSize = count($customers);
                $currentCustomerPage++;
                foreach ($customers as $customer) {
                    $currentOrdersPage = 1;
                    do {
                        $customerOrders = $this->getCustomerOrders($currentOrdersPage, $customer, $scope, $checkpoint);
                        if (empty($customerOrders)) {
                            $total->incrSkipped();
                            break;
                        }
                        $orders = $customerOrders->getOrders();
                        $ordersPageSize = count($orders);
                        $currentOrdersPage++;
                        $toExport[] = $customerOrders;

                        // Customer has too many orders. Let's export what we have and flush the cache.
                        if ($ordersPageSize == self::ORDER_PAGE_SIZE) {
                            $importResult = $this->autopilotClient->importOrders($scope, $toExport);
                            $total->incr($importResult);
                            if ($updateState !== null) {
                                $updateState($customersTotal, $customerPageSize, $total->toJSON());
                            }
                            $toExport = [];
                        }
                    } while ($ordersPageSize == self::ORDER_PAGE_SIZE);
                }
            }
        } while ($customerPageSize === self::CUSTOMER_PAGE_SIZE);

        if (!empty($toExport)) {
            $importResult = $this->autopilotClient->importOrders($scope, $toExport);
            $total->incr($importResult);
            if ($updateState !== null) {
                $updateState($customersTotal, $customerPageSize, $total->toJSON());
            }
        }

        return $total;
    }

    /**
     * @param int $page
     * @return array
     */
    private function getCustomers(int $page)
    {
        $attrCollection = $this->attrCollectionFactory->create();
        $attributes = $attrCollection->getAll($page, self::CUSTOMER_PAGE_SIZE);
        $customerIds = [];
        foreach ($attributes as $attr) {
            $customerIds[] = $attr->getCustomerId();
        }
        $collection = $this->customerCollectionFactory->create();
        $collection->setCurPage($page)
            ->setPageSize(self::CUSTOMER_PAGE_SIZE)
            ->addFieldToSelect(self::CUSTOMER_ID)
            ->addFieldToSelect(CustomerInterface::EMAIL)
            ->addFieldToFilter(self::CUSTOMER_ID, ['in' => $customerIds]);
        return [
            self::ITEMS_KEY => $collection->getItems(),
            self::TOTAL_KEY => $collection->getSize(),
        ];
    }

    /**
     * @param int $page
     * @param CustomerInterface $customer
     * @param ConfigScopeInterface $scope
     * @param DateTime|null $checkpoint
     * @return CustomerOrder|false
     */
    private function getCustomerOrders(
        int $page,
        $customer,
        ConfigScopeInterface $scope,
        ?DateTime $checkpoint = null
    ) {
        $this->searchCriteriaBuilder->setPageSize(self::ORDER_PAGE_SIZE)
            ->setCurrentPage($page)
            ->addFilter(OrderInterface::STORE_ID, $scope->getStoreIds(), 'in');

        if (!empty($checkpoint)) {
            $this->searchCriteriaBuilder->addFilter(OrderInterface::UPDATED_AT, $checkpoint, 'gt');
        }
        $customerId = (int)$customer->getId();
        $this->searchCriteriaBuilder->addFilter(OrderInterface::CUSTOMER_ID, $customerId);
        $sortOrder = $this->sortOrderBuilder->setField(OrderInterface::CREATED_AT)->setDirection(SortOrder::SORT_ASC);
        $this->searchCriteriaBuilder->addSortOrder($sortOrder->create());
        $orders = $this->orderRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        if (empty($orders)) {
            return false;
        }
        $result = $this->customerOrderFactory->create();
        $result->setCustomerId($customerId);
        $result->setCustomerEmail($customer->getEmail());
        $result->setOrders($orders);
        return $result;
    }
}
