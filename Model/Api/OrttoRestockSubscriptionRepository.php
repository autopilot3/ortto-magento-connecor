<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Api;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\ProductAlert\Model\Stock;
use Magento\Store\Model\ScopeInterface;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\OrttoCustomerRepositoryInterface;
use Ortto\Connector\Api\OrttoProductRepositoryInterface;
use Ortto\Connector\Api\OrttoRestockSubscriptionRepositoryInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Ortto\Connector\Model\Data\OrttoRestockSubscriptionFactory;
use Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory;
use Ortto\Connector\Model\Data\ListRestockSubscriptionResponseFactory;

class OrttoRestockSubscriptionRepository implements OrttoRestockSubscriptionRepositoryInterface
{
    const STORE_ID = 'store_id';
    const ADD_DATE = 'add_date';

    private OrttoLogger $logger;
    private OrttoRestockSubscriptionFactory $subscriptionFactory;
    private CollectionFactory $stockCollection;
    private ListRestockSubscriptionResponseFactory $listRestockSubscriptionResponseFactory;
    private OrttoCustomerRepositoryInterface $customerRepository;
    private OrttoProductRepositoryInterface $productRepository;
    private Data $helper;

    public function __construct(
        OrttoLogger $logger,
        Data $helper,
        OrttoRestockSubscriptionFactory $subscriptionFactory,
        \Ortto\Connector\Model\Data\ListRestockSubscriptionResponseFactory $listRestockSubscriptionResponseFactory,
        CollectionFactory $stockCollection,
        \Ortto\Connector\Api\OrttoCustomerRepositoryInterface $customerRepository,
        \Ortto\Connector\Api\OrttoProductRepositoryInterface $productRepository
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->stockCollection = $stockCollection;
        $this->listRestockSubscriptionResponseFactory = $listRestockSubscriptionResponseFactory;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
    }

    /** @inheirtDoc */
    public function getList(
        ConfigScopeInterface $scope,
        bool $newsletter,
        bool $crossStore,
        int $page,
        string $checkpoint,
        int $pageSize,
        array $data = []
    ) {
        $collection = $this->stockCollection->create()
            ->setCurPage($page)
            ->addFieldToSelect('*')
            ->setPageSize($pageSize)
            ->setOrder(self::ADD_DATE, SortOrder::SORT_ASC)
            ->addWebsiteFilter($scope->getWebsiteId())
            ->addFieldToFilter(self::STORE_ID, ['eq' => $scope->getId()]);

        if (!empty($checkpoint)) {
            $collection->addFieldToFilter(self::ADD_DATE, ['gteq' => $checkpoint]);
        }

        $result = $this->listRestockSubscriptionResponseFactory->create();
        $total = To::int($collection->getSize());
        $result->setTotal($total);
        if ($total == 0) {
            return $result;
        }

        $productIds = [];
        $customerIds = [];
        $stockSubscriptions = $collection->getItems();
        /** @var Stock $subscription */
        foreach ($stockSubscriptions as $subscription) {
            $productIds[] = To::int($subscription->getProductId());
            $customerIds[] = To::int($subscription->getCustomerId());
        }

        $customers = $this->customerRepository->getByIds($scope, $newsletter, $crossStore, $customerIds)->getItems();
        $products = $this->productRepository->getByIds($scope, $productIds)->getItems();
        $subscriptions = [];
        /** @var Stock $subscription */
        foreach ($stockSubscriptions as $subscription) {
            if ($restockSubscription = $this->convert($subscription, $customers, $products)) {
                $subscriptions[] = $restockSubscription;
            }
        }
        $result->setItems($subscriptions);
        $result->setHasMore($page < $total / $pageSize);
        return $result;
    }

    /**
     * @param Stock $subscription
     * @return \Ortto\Connector\Api\Data\OrttoRestockSubscriptionInterface|null
     */
    private function convert($subscription, $customers, $products)
    {
        $productId = To::int($subscription->getProductId());
        $product = $products[$productId];
        if ($product == null) {
            $this->logger->warn("Restock subscription product was not found", ['product_id' => $productId]);
            return null;
        }
        $customerId = To::int($subscription->getCustomerId());
        $customer = $customers[$customerId];
        if ($customer == null) {
            $this->logger->warn("Restock subscription customer was not found", ['customer_id' => $customerId]);
            return null;
        }
        $data = $this->subscriptionFactory->create();
        $data->setProduct($product);
        $data->setCustomer($customer);
        $data->setDateAdded($this->helper->toUTC($subscription->getAddDate()));
        return $data;
    }
}
