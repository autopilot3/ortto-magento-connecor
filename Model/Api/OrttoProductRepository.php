<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Api;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\ResourceConnection;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\Data\OrttoProductParentGroupInterface;
use Ortto\Connector\Api\OrttoProductRepositoryInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Bundle\Model\ResourceModel\Selection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Api\LinkRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface
    as CheckSourceItemSupport;
use Ortto\Connector\Model\Data\ListProductResponseFactory;
use Ortto\Connector\Model\Data\OrttoDownloadLinkFactory;
use Ortto\Connector\Model\Data\OrttoProductFactory;
use Ortto\Connector\Model\Data\OrttoProductParentGroupFactory;

class OrttoProductRepository implements OrttoProductRepositoryInterface
{
    private const IS_IN_STOCK = 'is_in_stock';
    private const STOCK_QUANTITY = 'qty';
    private const STOCK_NAME = 'stock_name';
    private Data $helper;
    private OrttoLogger $logger;
    private GetSalableQuantityDataBySku $salableQty;
    private Configurable $configurable;
    private Grouped $grouped;
    private Selection $bundle;
    private CheckSourceItemSupport $checkSourceItemSupport;
    private OrttoProductFactory $productFactory;
    private OrttoDownloadLinkFactory $downloadLinkFactory;
    private OrttoProductParentGroupFactory $productParentGroupFactory;
    private LinkRepositoryInterface $linkRepository;
    private ProductCollectionFactory $productCollectionFactory;
    private ListProductResponseFactory $listResponseFactory;
    private ResourceConnection $resourceConnection;
    private \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository;
    private \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory $stockItemCriteria;


    public function __construct(
        Data $helper,
        OrttoLogger $logger,
        GetSalableQuantityDataBySku $salableQty,
        Configurable $configurable,
        Grouped $grouped,
        Selection $bundle,
        LinkRepositoryInterface $linkRepository,
        CheckSourceItemSupport $checkSourceItemSupport,
        OrttoProductFactory $productFactory,
        OrttoDownloadLinkFactory $downloadLinkFactory,
        OrttoProductParentGroupFactory $productParentGroupFactory,
        ProductCollectionFactory $productCollectionFactory,
        ListProductResponseFactory $listResponseFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory $stockItemCriteria
    ) {
        $this->helper = $helper;
        $this->salableQty = $salableQty;
        $this->logger = $logger;
        $this->configurable = $configurable;
        $this->grouped = $grouped;
        $this->bundle = $bundle;
        $this->checkSourceItemSupport = $checkSourceItemSupport;
        $this->productFactory = $productFactory;
        $this->downloadLinkFactory = $downloadLinkFactory;
        $this->productParentGroupFactory = $productParentGroupFactory;
        $this->linkRepository = $linkRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->listResponseFactory = $listResponseFactory;
        $this->resourceConnection = $resourceConnection;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemCriteria = $stockItemCriteria;
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
        $collection = $this->productCollectionFactory->create()
            ->setCurPage($page)
            ->addAttributeToSelect('*')
            ->setPageSize($pageSize)
            ->setOrder(ProductInterface::UPDATED_AT, SortOrder::SORT_ASC)
            ->addWebsiteFilter($scope->getWebsiteId());

        if (!empty($checkpoint)) {
            $collection->addFieldToFilter(ProductInterface::UPDATED_AT, ['gteq' => $checkpoint]);
        }
        $result = $this->listResponseFactory->create();
        $total = To::int($collection->getSize());
        $result->setTotal($total);
        if ($total == 0) {
            return $result;
        }
        $storeId = $scope->getId();
        $productList = [];
        $websiteStockName = $this->getWebsiteStockName($scope);
        /** @var  Product $product */
        foreach ($collection as $product) {
            $productList[] = $this->convert($product, $storeId, $websiteStockName);
        }
        $result->setItems($productList);
        $result->setHasMore($page < $total / $pageSize);

        return $result;
    }

    /** @inheirtDoc */
    public function getByIds(ConfigScopeInterface $scope, $productIds, $data = [])
    {
        $result = $this->listResponseFactory->create();
        if (empty($productIds)) {
            return $result;
        }
        $productIds = array_unique($productIds, SORT_NUMERIC);
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addIdFilter($productIds);

        $total = To::int($collection->getSize());
        $result->setTotal($total);
        if ($total == 0) {
            return $result;
        }

        $products = [];
        foreach ($productIds as $productId) {
            // The make sure all the keys always exist in the result array, even if the requested
            // product was not found!
            $products[$productId] = null;
        }
        $storeId = $scope->getId();
        $websiteStockName = $this->getWebsiteStockName($scope);
        /** @var  Product $product */
        foreach ($collection->getItems() as $product) {
            $p = $this->convert($product, $storeId, $websiteStockName);
            $products[To::int($p->getId())] = $p;
        }

        $result->setItems($products);
        return $result;
    }


    /** @inheirtDoc */
    public function getById(ConfigScopeInterface $scope, int $productId, array $data = [])
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addIdFilter($productId)->addAttributeToSelect('*');
        /** @var Product $product */
        $product = $collection->getItemById($productId);
        if (empty($product)) {
            return null;
        }
        return $this->convert($product, $scope->getId(), $this->getWebsiteStockName($scope));
    }

    /**
     * @param Product $product
     * @param int $storeID
     * @param string $websiteStockName
     * @return \Ortto\Connector\Api\Data\OrttoProductInterface
     */
    private function convert($product, int $storeID, string $websiteStockName)
    {
        $product->setStoreId($storeID);
        $orttoProduct = $this->productFactory->create();
        $productType = $product->getTypeId();
        $productId = To::int($product->getId());
        $sku = $product->getSku();
        $orttoProduct->setId($productId);
        $orttoProduct->setType($productType);
        $orttoProduct->setName(html_entity_decode($product->getName()));
        $orttoProduct->setSku($sku);
        $orttoProduct->setPrice(To::float($product->getPrice()));
        $orttoProduct->setMinimalPrice(To::float($product->getMinimalPrice()));
        $orttoProduct->setCalculatedPrice(To::float($product->getCalculatedFinalPrice()));
        $orttoProduct->setUpdatedAt($this->helper->toUTC($product->getUpdatedAt()));
        $orttoProduct->setCreatedAt($this->helper->toUTC($product->getCreatedAt()));
        $orttoProduct->setWeight(To::float($product->getWeight()));
        $orttoProduct->setIsVisible($product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE);
        $orttoProduct->setShortDescription((string)$product->getData('short_description'));
        $orttoProduct->setDescription((string)$product->getData('description'));
        $orttoProduct->setIsOptionRequired(To::int($product->getData('required_options')) > 0);
        $orttoProduct->setCurrencyCode($product->getStore()->getCurrentCurrencyCode());
        $stock = $this->getStockData($productId, $sku, $productType, $websiteStockName);
        $orttoProduct->setStockQuantity($stock[self::STOCK_QUANTITY]);
        $orttoProduct->setIsInStock($stock[self::IS_IN_STOCK]);

        switch ($productType) {
            case Configurable::TYPE_CODE:
            case Grouped::TYPE_CODE:
            case "bundle":
                $children = [];
                foreach ($product->getTypeInstance()->getChildrenIds($productId) as $idGroup) {
                    foreach ($idGroup as $id) {
                        $children[] = To::int($id);
                    }
                }
                $orttoProduct->setChildren($children);
                if ($orttoProduct->getPrice() == 0) {
                    $finalPrice = $product->getPriceInfo()->getPrice('final_price');
                    $orttoProduct->setPrice(To::float($finalPrice->getMinimalPrice()->getValue()));
                }
                break;
            case "simple":
            case "virtual":
                $orttoProduct->setParents($this->getParentIds($productId));
                break;
            case "downloadable":
                $links = [];
                foreach ($this->linkRepository->getLinksByProduct($product) as $link) {
                    $downloadLink = $this->downloadLinkFactory->create();
                    $downloadLink->setTitle($link->getTitle() ?? '');
                    $downloadLink->setDownloads(To::int($link->getNumberOfDownloads()));
                    $downloadLink->setType($link->getLinkType());
                    $downloadLink->setUrl($link->getLinkUrl());
                    $downloadLink->setFile($link->getLinkFile() ?? '');
                    $downloadLink->setSampleType($link->getSampleType());
                    $downloadLink->setSampleUrl($link->getSampleUrl());
                    $downloadLink->setSampleFile($link->getSampleFile() ?? '');
                    $downloadLink->setPrice(To::float($link->getPrice()));
                    $links[] = $downloadLink;
                }
                $orttoProduct->setLinks($links);
                $orttoProduct->setParents($this->getParentIds($productId));
                break;
        }

        // Categories
        $categoryIDs = [];
        foreach ($product->getCategoryIds() as $categoryId) {
            $categoryIDs[] = To::int($categoryId);
        }
        $orttoProduct->setCategoryIds($categoryIDs);

        // URLs
        $orttoProduct->setImageUrl($this->helper->getProductImageURL($product));
        if ($orttoProduct->getIsVisible()) {
            $orttoProduct->setUrl($product->getUrlModel()->getUrlInStore($product, ['_escape' => true]));
        }

        return $orttoProduct;
    }

    private function getStockData(
        int $productId,
        string $productSKU,
        string $productType,
        string $websiteStockName
    ): array {
        $result = [
            self::STOCK_QUANTITY => 0.0,
            self::IS_IN_STOCK => false,
        ];
        if ($this->checkSourceItemSupport->execute($productType)) {
            $stocks = $this->salableQty->execute($productSKU);
            if (empty($stocks)) {
                return $result;
            }
            if (count($stocks) == 1 && isset($stocks[0][self::STOCK_QUANTITY])) {
                $quantity = To::float($stocks[0][self::STOCK_QUANTITY]);
                return [
                    self::STOCK_QUANTITY => $quantity,
                    self::IS_IN_STOCK => $quantity > 0,
                ];
            }

            if ($websiteStockName != '') {
                $quantity = 0;
                foreach ($stocks as $stock) {
                    if (isset($stock[self::STOCK_NAME])
                        && $stock[self::STOCK_NAME] == $websiteStockName
                        && isset($stock[self::STOCK_QUANTITY])) {
                        $quantity += To::float($stock[self::STOCK_QUANTITY]);
                    }
                }
                return [
                    self::STOCK_QUANTITY => $quantity,
                    self::IS_IN_STOCK => $quantity > 0,
                ];
            }
        } else {
            $criteria = $this->stockItemCriteria->create();
            $criteria->setProductsFilter($productId);
            $stockItems = $this->stockItemRepository->getList($criteria)->getItems();
            $quantity = 0;
            $isInStock = false;
            foreach ($stockItems as $stockItem) {
                $quantity += To::float($stockItem->getQty());
                if (!$isInStock) {
                    $isInStock = To::bool($stockItem->getIsInStock());
                }
            }
            return [
                self::STOCK_QUANTITY => $quantity,
                self::IS_IN_STOCK => $isInStock,
            ];
        }
    }

    private function getParentIds(int $productId): OrttoProductParentGroupInterface
    {
        $parentGroup = $this->productParentGroupFactory->create();
        $parentGroup->setBundle($this->bundle->getParentIdsByChild($productId) ?? []);
        $parentGroup->setGrouped($this->grouped->getParentIdsByChild($productId) ?? []);
        $parentGroup->setConfigurable($this->configurable->getParentIdsByChild($productId) ?? []);
        return $parentGroup;
    }

    private function getWebsiteStockName(ConfigScopeInterface $scope): string
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $stockTable = $connection->getTableName('inventory_stock');
            $salesChannelTable = $connection->getTableName('inventory_stock_sales_channel');
            $sql = sprintf(
                "SELECT s.name
                    FROM %s s INNER JOIN %s sc ON s.stock_id = sc.stock_id
                    WHERE sc.code = '%s';",
                $stockTable,
                $salesChannelTable,
                $scope->getWebsiteCode()
            );
            return (string)$connection->fetchOne($sql);
        } catch (\Exception $e) {
            $this->logger->error($e, sprintf("Failed to fetch %s website's stock name", $scope->getWebsiteCode()));
            return '';
        }
    }
}
