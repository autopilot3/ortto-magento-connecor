<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Api;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\OrttoProductCategoryRepositoryInterface;
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

    public function __construct(
        OrttoLogger $logger,
        OrttoProductCategoryFactory $productCategoryFactory,
        CollectionFactory $categoryCollection,
        \Ortto\Connector\Model\Data\ListProductCategoryResponseFactory $listProductCategoryResponseFactory
    ) {
        $this->logger = $logger;
        $this->productCategoryFactory = $productCategoryFactory;
        $this->categoryCollection = $categoryCollection;
        $this->listProductCategoryResponseFactory = $listProductCategoryResponseFactory;
    }

    public function getList(ConfigScopeInterface $scope, int $page, string $checkpoint, int $pageSize, array $data = [])
    {
        $collection = $this->categoryCollection->create();
        $collection->setPage($page, $pageSize)
            ->addFieldToSelect("*")
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
        /** @var CategoryInterface $category */
        foreach ($collection->getItems() as $category) {
            $categories[] = $this->convert($category);
        }
        $result->setItems($categories);
        $result->setHasMore($page < $total / $pageSize);
        return $result;
    }

    /**
     * @param CategoryInterface $category
     * @return \Ortto\Connector\Api\Data\OrttoProductCategoryInterface
     */
    private function convert($category)
    {
        $data = $this->productCategoryFactory->create();
        $data->setId(To::int($category->getEntityId()));
        $data->setName((string)$category->getName());
        $data->setDescription((string)$category->getDescription() ?? '');
        $data->setProductsCount(To::int($category->getProductCount()));
        try {
            if ($imageURL = $category->getImageUrl()) {
                $data->setImageURL((string)$imageURL);
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e, "Failed to fetch product category image");
        }
        return $data;
    }
}
