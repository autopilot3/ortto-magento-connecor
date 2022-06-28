<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Api;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\Data\OrttoProductParentGroupInterface;
use Ortto\Connector\Api\OrttoProductRepositoryInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Bundle\Model\ResourceModel\Selection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\ImageFactory;
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
use Ortto\Connector\Model\Data\OrttoStockFactory;
use Ortto\Connector\Model\Data\OrttoStockItemFactory;

class OrttoProductRepository implements OrttoProductRepositoryInterface
{
    private const NO_SELECT = 'no_select';
    private const ENTITY_ID = 'entity_id';

    private Data $helper;
    private ImageFactory $imageFactory;
    private OrttoLogger $logger;
    private GetSalableQuantityDataBySku $salableQty;
    private Configurable $configurable;
    private Grouped $grouped;
    private Selection $bundle;
    private CheckSourceItemSupport $checkSourceItemSupport;
    private OrttoProductFactory $productFactory;
    private OrttoStockItemFactory $stockItemFactory;
    private OrttoDownloadLinkFactory $downloadLinkFactory;
    private OrttoStockFactory $stockFactory;
    private OrttoProductParentGroupFactory $productParentGroupFactory;
    private LinkRepositoryInterface $linkRepository;
    private ProductCollectionFactory $productCollectionFactory;
    private ListProductResponseFactory $listResponseFactory;

    public function __construct(
        Data $helper,
        ImageFactory $imageFactory,
        OrttoLogger $logger,
        GetSalableQuantityDataBySku $salableQty,
        Configurable $configurable,
        Grouped $grouped,
        Selection $bundle,
        LinkRepositoryInterface $linkRepository,
        CheckSourceItemSupport $checkSourceItemSupport,
        OrttoProductFactory $productFactory,
        OrttoStockItemFactory $stockItemFactory,
        OrttoDownloadLinkFactory $downloadLinkFactory,
        OrttoStockFactory $stockFactory,
        OrttoProductParentGroupFactory $productParentGroupFactory,
        ProductCollectionFactory $productCollectionFactory,
        ListProductResponseFactory $listResponseFactory
    ) {
        $this->helper = $helper;
        $this->imageFactory = $imageFactory;
        $this->salableQty = $salableQty;
        $this->logger = $logger;
        $this->configurable = $configurable;
        $this->grouped = $grouped;
        $this->bundle = $bundle;
        $this->checkSourceItemSupport = $checkSourceItemSupport;
        $this->productFactory = $productFactory;
        $this->stockItemFactory = $stockItemFactory;
        $this->downloadLinkFactory = $downloadLinkFactory;
        $this->stockFactory = $stockFactory;
        $this->productParentGroupFactory = $productParentGroupFactory;
        $this->linkRepository = $linkRepository;
        $this->productCollectionFactory = $productCollectionFactory;
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
        $collection = $this->productCollectionFactory->create()
            ->setCurPage($page)
            ->addAttributeToSelect('*')
            ->setPageSize($pageSize)
            ->setOrder('entity_id', 'DESC')// Newer products first
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
        /** @var  Product $product */
        foreach ($collection->getItems() as $product) {
            $productList[] = $this->convert($product, $storeId);
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
        $productIds = array_unique($productIds);
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addFieldToFilter(self::ENTITY_ID, ['in' => $productIds]);

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
        /** @var  Product $product */
        foreach ($collection->getItems() as $product) {
            $p = $this->convert($product, $storeId);
            $products[$p->getId()] = $p;
        }

        $result->setItems($products);
        return $result;
    }

    /**
     * @param Product $product
     * @param int $storeID
     * @return \Ortto\Connector\Api\Data\OrttoProductInterface
     */
    private function convert($product, int $storeID)
    {
        $product->setStoreId($storeID);
        $orttoProduct = $this->productFactory->create();
        $productType = $product->getTypeId();
        $productId = To::int($product->getId());
        $sku = $product->getSku();
        $orttoProduct->setId($productId);
        $orttoProduct->setType($productType);
        $orttoProduct->setName($product->getName());
        $orttoProduct->setSku($sku);
        $orttoProduct->setPrice(To::float($product->getPrice()));
        $orttoProduct->setMinimalPrice(To::float($product->getMinimalPrice()));
        $orttoProduct->setCalculatedPrice(To::float($product->getCalculatedFinalPrice()));
        $orttoProduct->setUpdatedAt($this->helper->toUTC($product->getUpdatedAt()));
        $orttoProduct->setCreatedAt($this->helper->toUTC($product->getCreatedAt()));
        $orttoProduct->setWeight(To::float($product->getWeight()));
        $orttoProduct->setIsVisible($product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE);
        $orttoProduct->setShortDescription($product->getShortDescription() ?? '');
        $orttoProduct->setDescription($product->getDescription() ?? '');
        $orttoProduct->setIsOptionRequired(To::int($product->getData('required_options')) > 0);
        $orttoProduct->setCurrencyCode($product->getStore()->getCurrentCurrencyCode());


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

        // Stock Data
        $total = 0.0;
        if ($this->checkSourceItemSupport->execute($productType)) {
            $salableItems = $this->salableQty->execute($sku);
            $stocks = [];
            foreach ($salableItems as $salable) {
                $quantity = To::float($salable['qty']);
                $total += $quantity;
                $stock = $this->stockFactory->create();
                $stock->setName($salable['stock_name']);
                $stock->setQuantity($quantity);
                $stock->setIsManage(To::bool($salable['manage_stock']));
                $stocks[] = $stock;
            }
            $orttoProduct->setStocks($stocks);
        }
        $stockItem = $this->stockItemFactory->create();
        $stockItem->setQuantity($total);
        $stockItem->setIsSalable(To::bool($product->isSalable()));
        $stockItem->setIsInStock(To::bool($product->isInStock()));
        $orttoProduct->setStock($stockItem);

        // URLs
        $image = $product->getImage();
        if (!empty($image) && $image != self::NO_SELECT) {
            $orttoProduct->setImageUrl($this->resolveProductImageURL($product));
        }
        $orttoProduct->setUrl($product->getUrlModel()->getUrlInStore($product, ['_escape' => true]));

        return $orttoProduct;
    }

    /**
     * @param Product $product
     */
    private function resolveProductImageURL($product): string
    {
        $img = $this->imageFactory->create();
        return $img->init($product, 'product_page_image_small')
                ->setImageFile($product->getImage())->getUrl() ?? '';
    }

    private function getParentIds(int $productId): OrttoProductParentGroupInterface
    {
        $parentGroup = $this->productParentGroupFactory->create();
        $parentGroup->setBundle($this->bundle->getParentIdsByChild($productId) ?? []);
        $parentGroup->setGrouped($this->grouped->getParentIdsByChild($productId) ?? []);
        $parentGroup->setConfigurable($this->configurable->getParentIdsByChild($productId) ?? []);
        return $parentGroup;
    }
}
