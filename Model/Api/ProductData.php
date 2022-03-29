<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Model\Api;

use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Helper\To;
use Autopilot\AP3Connector\Logger\Logger;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\Product\Visibility;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;

class ProductData
{
    private const VARIATIONS = 'variations';
    private const NO_SELECT = 'no_select';

    private Product $product;
    private string $imageURL;
    private string $url;

    /** @var ProductData[] $variations */
    private array $variations;
    private array $variationsData;
    private array $stockData;
    private array $stocks;

    private array $categoriesData;
    private bool $isConfigurable;

    private Data $helper;
    private ProductRepository $productRepository;
    private ImageFactory $imageFactory;
    private CategoryCollectionFactory $categoryCollectionFactory;
    private Configurable $configurable;
    private Logger $logger;
    private ProductDataFactory $productDataFactory;
    private GetSalableQuantityDataBySku $salableQty;

    public function __construct(
        Data $helper,
        ProductRepository $productRepository,
        ImageFactory $imageFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        Configurable $configurable,
        Logger $logger,
        ProductDataFactory $productDataFactory,
        GetSalableQuantityDataBySku $salableQty
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->imageFactory = $imageFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->configurable = $configurable;
        $this->productDataFactory = $productDataFactory;
        $this->salableQty = $salableQty;
        $this->logger = $logger;
        $this->categoriesData = [];
        $this->variations = [];
        $this->variationsData = [];
        $this->stockData = [];
        $this->stocks = [];
        $this->isConfigurable = false;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function loadById(int $id)
    {
        try {
            /** @var Product $product */
            $product = $this->productRepository->getById($id);
            $this->load($product);
            return true;
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e, sprintf("Product ID %d could not be found.", $id));
            return false;
        }
    }

    /**
     * @param Product $product
     * @return void
     */
    public function load($product)
    {
        $this->product = $product;
        $this->loadCategories();
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $this->isConfigurable = true;
            $this->loadVariations();
        }
        $this->loadStockData();
        $this->loadURLs();
    }

    public function toArray(): array
    {
        if (empty($this->product)) {
            return [];
        }
        $productTypeId = $this->product->getTypeId();
        $fields = [
            'id' => To::int($this->product->getId()),
            'type' => $productTypeId,
            'name' => (string)$this->product->getName(),
            'sku' => (string)$this->product->getSku(),
            'url' => $this->url,
            'image_url' => $this->imageURL,
            'is_virtual' => To::bool($this->product->getIsVirtual()),
            'categories' => $this->categoriesData,
            'price' => To::float($this->product->getPrice()),
            'minimal_price' => To::float($this->product->getMinimalPrice()),
            'calculated_price' => To::float($this->product->getCalculatedFinalPrice()),
            'updated_at' => $this->helper->toUTC($this->product->getUpdatedAt()),
            'created_at' => $this->helper->toUTC($this->product->getCreatedAt()),
            'weight' => To::float($this->product->getWeight()),
            // Stock total, based on the available stocks
            'stock' => $this->stockData,
            // Each product can have multiple stocks
            'stocks' => $this->stocks,
            'custom_attributes' => [],
            self::VARIATIONS => $this->variationsData,
        ];

        $customAttrs = $this->product->getCustomAttributes();
        foreach ($customAttrs as $attr) {
            $fields['custom_attributes'][] = [
                'code' => $attr->getAttributeCode(),
                'value' => $attr->getValue(),
            ];
        }

        return $fields;
    }

    private function loadVariations()
    {
        $childrenIDs = $this->configurable->getChildrenIds($this->product->getId());
        foreach ($childrenIDs as $idGroup) {
            foreach ($idGroup as $productId) {
                $variation = $this->productDataFactory->create();
                if ($variation->loadById(To::int($productId))) {
                    $this->variations[] = $variation;
                    $this->variationsData[] = $variation->toArray();
                }
            }
        }
    }

    private function loadCategories()
    {
        $ids = $this->product->getCategoryIds();
        if (empty($ids)) {
            return;
        }
        $collection = $this->categoryCollectionFactory->create();
        $collection->addFieldToSelect("*")
            ->addFieldToFilter('entity_id', ['in' => implode(',', $ids)]);

        /** @var CategoryInterface $category */
        foreach ($collection->getItems() as $category) {
            $this->categoriesData[] = $this->getCategoryData($category);
        }
    }

    private function getCategoryData(CategoryInterface $category): array
    {
        return [
            'id' => To::int($category->getId()),
            'name' => $category->getName(),
            'is_active' => To::bool($category->getIsActive()),
            'level' => To::int($category->getLevel()),
        ];
    }

    private function loadURLs()
    {
        $image = $this->product->getImage();
        $productId = To::int($this->product->getId());
        if (!empty($image) && $image != self::NO_SELECT) {
            $this->imageURL = $this->resolveProductImageURL($this->product);
        }

        $visible = $this->product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE;
        $imageLoaded = !empty($this->imageURL);
        if ($visible) {
            $this->url = $this->product->getProductUrl();
        }
        if (!$imageLoaded || !$visible) {
            $parent = $this->getParent($productId);
            if (!$parent) {
                return;
            }
            if (!$imageLoaded) {
                $image = $parent->getImage();
                if (!empty($image) && $image != self::NO_SELECT) {
                    $this->imageURL = $this->resolveProductImageURL($parent);
                }
            }
            if (!$visible) {
                $this->url = $parent->getProductUrl();
            }
        }
    }

    private function loadStockData()
    {
        $salableItems = $this->salableQty->execute($this->product->getSku());
        $total = 0.0;
        foreach ($salableItems as $salable) {
            $quantity = To::float($salable['qty']);
            $total += $quantity;
            $this->stocks[] = [
                'name' => $salable['stock_name'],
                'quantity' => $quantity,
                'is_manage' => To::bool($salable['manage_stock']),
            ];
        }
        $this->stockData = [
            'is_in_stock' => To::bool($this->product->isInStock()),
            'is_salable' => To::bool($this->product->isSalable()),
            'quantity' => $total,
        ];
    }

    /**
     * @param Product|ProductInterface $product
     */
    private function resolveProductImageURL($product): string
    {
        $img = $this->imageFactory->create();
        return $img->init($product, 'product_page_image_small')
                ->setImageFile($product->getImage())->getUrl() ?? '';
    }

    /**
     * @param int $productId
     * @return false|Product
     */
    private function getParent(int $productId)
    {
        $parentIds = $this->configurable->getParentIdsByChild($productId);
        foreach ($parentIds as $id) {
            try {
                $parent = $this->productRepository->getById($id, false);
                if ($parent->getTypeId() == Configurable::TYPE_CODE) {
                    return $parent;
                }
            } catch (NoSuchEntityException $e) {
                $this->logger->warn("Failed to lookup product by ID", ['error' => $e->getMessage()]);
            }
        }
        return false;
    }

    /**
     * @return array|null
     */
    public function getVariationDataBySKU(string $sku)
    {
        if (!$this->isConfigurable) {
            return null;
        }
        foreach ($this->variations as $index => $variation) {
            if ($sku == $variation->product->getSku()) {
                return $this->variationsData[$index];
            }
        }
        return null;
    }
}
