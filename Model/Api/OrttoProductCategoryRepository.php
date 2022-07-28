<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Api;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\LocalizedException;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\OrttoProductCategoryRepositoryInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Ortto\Connector\Model\Data\OrttoProductCategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Ortto\Connector\Model\Data\ListProductCategoryResponseFactory;

class OrttoProductCategoryRepository implements OrttoProductCategoryRepositoryInterface
{
    private OrttoLogger $logger;
    private OrttoProductCategoryFactory $productCategoryFactory;
    private CollectionFactory $categoryCollection;
    private ListProductCategoryResponseFactory $listProductCategoryResponseFactory;
    private Data $helper;

    public function __construct(
        Data $helper,
        OrttoLogger $logger,
        OrttoProductCategoryFactory $productCategoryFactory,
        CollectionFactory $categoryCollection,
        \Ortto\Connector\Model\Data\ListProductCategoryResponseFactory $listProductCategoryResponseFactory
    ) {
        $this->logger = $logger;
        $this->productCategoryFactory = $productCategoryFactory;
        $this->categoryCollection = $categoryCollection;
        $this->listProductCategoryResponseFactory = $listProductCategoryResponseFactory;
        $this->helper = $helper;
    }

    public function getList(ConfigScopeInterface $scope, int $page, string $checkpoint, int $pageSize, array $data = [])
    {
        $collection = $this->categoryCollection->create();
        $collection->setPage($page, $pageSize)
            ->addFieldToSelect("*")
            ->addIsActiveFilter()
            ->setOrder(CategoryInterface::KEY_UPDATED_AT, SortOrder::SORT_ASC)
            ->setStoreId($scope->getId());

        if (!empty($checkpoint)) {
            $collection->addFieldToFilter(CategoryInterface::KEY_UPDATED_AT, ['gteq' => $checkpoint]);
        }

        $result = $this->listProductCategoryResponseFactory->create();
        $total = To::int($collection->getSize());
        $result->setTotal($total);
        if ($total == 0) {
            return $result;
        }

        $categories = [];
        /** @var Category $category */
        foreach ($collection->getItems() as $category) {
            $productCount = To::int($category->getProductCount());
//            if ($productCount <= 0) {
//                continue;
//            }
            $categories[] = $this->convert($category, $productCount);
        }
        $result->setItems($categories);
        $result->setHasMore($page < $total / $pageSize);
        return $result;
    }

    /**
     * @param Category $category
     * @param int $productCount
     * @return \Ortto\Connector\Api\Data\OrttoProductCategoryInterface
     */
    private function convert($category, int $productCount)
    {
        $data = $this->productCategoryFactory->create();
        $data->setId(To::int($category->getEntityId()));
        $data->setName((string)$category->getName());
        $data->setDescription((string)$category->getData('description') ?? '');
        $data->setProductsCount($productCount);
        $data->setCreatedAt($this->helper->toUTC($category->getCreatedAt()));
        $data->setUpdatedAt($this->helper->toUTC($category->getUpdatedAt()));
        $data->setFullName($this->getFullName($category));
        try {
            if ($imageURL = $category->getImageUrl()) {
                $data->setImageURL((string)$imageURL);
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e, "Failed to fetch product category image");
        }
        return $data;
    }

    private function getFullName(Category $category): string
    {
        $names = [];
        foreach ($category->getParentCategories() as $parent) {
            if ($category->getEntityId() != $parent->getEntityId() && $parent->getChildrenCount() > 1) {
                $names[] = $parent->getName();
            }
        }
        $names[] = $category->getName();
        return implode('/', $names);
    }
}
