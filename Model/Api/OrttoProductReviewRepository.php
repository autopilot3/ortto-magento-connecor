<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Api;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\OrttoCustomerRepositoryInterface;
use Ortto\Connector\Api\OrttoProductRepositoryInterface;
use Ortto\Connector\Api\OrttoProductReviewRepositoryInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Ortto\Connector\Model\Data\ListProductReviewResponseFactory;
use Ortto\Connector\Model\Data\OrttoProductReviewFactory;

class OrttoProductReviewRepository implements OrttoProductReviewRepositoryInterface
{
    private const PRODUCT_ID = 'product_id';
    private const CUSTOMER_ID = 'customer_id';
    private const NICKNAME = 'nickname';
    private const CREATED_AT = 'created_at';
    private const DETAIL = 'detail';
    private const TITLE = 'title';
    private const STATUS = 'status';

    private Data $helper;
    private OrttoLogger $logger;
    private ListProductReviewResponseFactory $listResponseFactory;
    private OrttoProductReviewFactory $productReviewFactory;
    private \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory;
    private OrttoProductRepositoryInterface $productRepository;
    private OrttoCustomerRepositoryInterface $customerRepository;

    /**
     * @param Data $helper
     * @param OrttoLogger $logger
     * @param ResourceConnection $resourceConnection
     * @param OrttoProductReviewFactory $productReviewFactory
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory
     * @param OrttoProductRepositoryInterface $productRepository
     * @param OrttoCustomerRepositoryInterface $customerRepository
     * @param ListProductReviewResponseFactory $listResponseFactory
     */
    public function __construct(
        Data $helper,
        OrttoLogger $logger,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ortto\Connector\Model\Data\OrttoProductReviewFactory $productReviewFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        OrttoProductRepositoryInterface $productRepository,
        OrttoCustomerRepositoryInterface $customerRepository,
        ListProductReviewResponseFactory $listResponseFactory
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->productReviewFactory = $productReviewFactory;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->listResponseFactory = $listResponseFactory;
    }

    /** @inheirtDoc */
    public function getList(ConfigScopeInterface $scope, int $page, string $checkpoint, int $pageSize, array $data = [])
    {
        if ($page < 1) {
            $page = 1;
        }
        if ($pageSize == 0) {
            $pageSize = 100;
        }

        $collection = $this->reviewCollectionFactory->create();
        $connection = $collection->getConnection();
        $reviewEntityTable = $connection->getTableName('review_entity');
        $reviewStatusTable = $connection->getTableName('review_status');
        $query = $collection->setPageSize($pageSize)
            ->setCurPage($page)
            ->getSelect()
            ->columns([
                self::CREATED_AT => 'main_table.' . self::CREATED_AT,
                self::PRODUCT_ID => 'main_table.entity_pk_value',
                self::DETAIL => 'detail.' . self::DETAIL,
                self::TITLE => 'detail.' . self::TITLE,
                self::NICKNAME => 'detail.' . self::NICKNAME,
                self::CUSTOMER_ID => 'detail.' . self::CUSTOMER_ID,
            ])
            ->order('main_table.created_at DESC')
            ->join(
                ['re' => $reviewEntityTable],
                'main_table.entity_id = re.entity_id',
                []
            )->join(
                ['rs' => $reviewStatusTable],
                'main_table.status_id = rs.status_id',
                [self::STATUS => 'rs.status_code']
            )->where("re.entity_code = ?", 'product')
            ->where("rs.status_code = ?", 'approved')
            ->where("detail.store_id = ?", $scope->getId())
            ->where("detail.customer_id IS NOT NULL");

        if (!empty($checkpoint)) {
            $query->where('main_table.' . self::CREATED_AT . ' >= ?', $checkpoint);
        }

        $items = $collection->getItems();

        $productIds = [];
        $customerIds = [];
        foreach ($items as $review) {
            $productIds[] = To::int($review->getData(self::PRODUCT_ID));
            $customerIds[] = To::int($review->getData(self::CUSTOMER_ID));
        }

        $customers = $this->customerRepository->getByIds($scope, $customerIds)->getItems();
        $products = $this->productRepository->getByIds($scope, $productIds)->getItems();

        $result = $this->listResponseFactory->create();
        $total = To::int($collection->getSize());
        $result->setTotal($total);
        if ($total == 0) {
            return $result;
        }
        $reviews = [];
        foreach ($collection->getItems() as $review) {
            $reviews[] = $this->convert($review, $products, $customers);
        }
        $result->setItems($reviews);
        $result->setHasMore($page < $total / $pageSize);

        return $result;
    }


    /** @inheirtDoc */
    public function getById(ConfigScopeInterface $scope, int $reviewId, array $data = [])
    {
        $collection = $this->reviewCollectionFactory->create();
        $connection = $collection->getConnection();
        $reviewEntityTable = $connection->getTableName('review_entity');
        $reviewStatusTable = $connection->getTableName('review_status');
        $collection->getSelect()
            ->columns([
                self::CREATED_AT => 'main_table.' . self::CREATED_AT,
                self::PRODUCT_ID => 'main_table.entity_pk_value',
                self::DETAIL => 'detail.' . self::DETAIL,
                self::TITLE => 'detail.' . self::TITLE,
                self::NICKNAME => 'detail.' . self::NICKNAME,
                self::CUSTOMER_ID => 'detail.' . self::CUSTOMER_ID,
            ])
            ->order('main_table.created_at DESC')
            ->join(
                ['re' => $reviewEntityTable],
                'main_table.entity_id = re.entity_id',
                []
            )->join(
                ['rs' => $reviewStatusTable],
                'main_table.status_id = rs.status_id',
                [self::STATUS => 'rs.status_code']
            )->where("re.entity_code = ?", 'product')
            ->where("re.entity_id = ?", $reviewId)
            ->where("detail.store_id = ?", $scope->getId());

        $review = $collection->getItemById($reviewId);
        if (empty($review)) {
            return null;
        }
        $data = $this->productReviewFactory->create();
        $data->setNickname(html_entity_decode((string)$review->getData(self::NICKNAME)));
        $data->setDetails(html_entity_decode((string)$review->getData(self::DETAIL)));
        $data->setTitle(html_entity_decode((string)$review->getData(self::TITLE)));
        $data->setStatus((string)$review->getData(self::STATUS));
        $product = $this->productRepository->getById($scope, To::int($review->getData(self::PRODUCT_ID)));
        if (!empty($product)) {
            $data->setProduct($product);
        }
        $customer = $this->customerRepository->getById(To::int($review->getData(self::CUSTOMER_ID)));
        if (!empty($customer)) {
            $data->setCustomer($customer);
        }
        return $data;
    }

    /**
     * @param DataObject $review
     * @param \Ortto\Connector\Api\Data\OrttoProductInterface[] $products
     * @param \Ortto\Connector\Api\Data\OrttoCustomerInterface[] $customers
     * @return \Ortto\Connector\Api\Data\OrttoProductReviewInterface
     */
    private function convert($review, $products, $customers)
    {
        $data = $this->productReviewFactory->create();
        $data->setCreatedAt($this->helper->toUTC((string)$review->getData(self::CREATED_AT)));
        $data->setNickname(html_entity_decode((string)$review->getData(self::NICKNAME)));
        $data->setDetails(html_entity_decode((string)$review->getData(self::DETAIL)));
        $data->setTitle(html_entity_decode((string)$review->getData(self::TITLE)));
        $data->setStatus((string)$review->getData(self::STATUS));
        $data->setProduct($products[To::int($review->getData(self::PRODUCT_ID))]);
        $data->setCustomer($customers[To::int($review->getData(self::CUSTOMER_ID))]);
        return $data;
    }
}
