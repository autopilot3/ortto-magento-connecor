<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Api;

use Ortto\Connector\Api\OrttoSerializerInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Bundle\Model\ResourceModel\Selection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Api\LinkRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\Product\Visibility;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface
    as CheckSourceItemSupport;

class ProductData
{
    private const CHILDREN = 'children';
    private const LINKS = 'links';
    private const BUNDLE = 'bundle';
    private const CONFIGURABLE = 'configurable';
    private const GROUPED = 'grouped';

    private const NO_SELECT = 'no_select';

    private Product $product;
    private string $imageURL;
    private string $url;
    private array $parents;
    private array $stockData;
    private array $stocks;
    /** @var int[] $children */
    private array $children;

    private array $categoriesData;
    /** @var LinkInterface[] $links */
    private array $links;

    private Data $helper;
    private ProductRepository $productRepository;
    private ImageFactory $imageFactory;
    private OrttoLogger $logger;
    private GetSalableQuantityDataBySku $salableQty;
    private Configurable $configurable;
    private Grouped $grouped;
    private Selection $bundle;
    private LinkRepositoryInterface $linkRepository;
    private CheckSourceItemSupport $checkSourceItemSupport;
    private OrttoSerializerInterface $serializer;

    public function __construct(
        Data $helper,
        ProductRepository $productRepository,
        ImageFactory $imageFactory,
        OrttoLogger $logger,
        GetSalableQuantityDataBySku $salableQty,
        Configurable $configurable,
        Grouped $grouped,
        Selection $bundle,
        LinkRepositoryInterface $linkRepository,
        CheckSourceItemSupport $checkSourceItemSupport,
        OrttoSerializerInterface $serializer
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->imageFactory = $imageFactory;
        $this->salableQty = $salableQty;
        $this->logger = $logger;
        $this->categoriesData = [];
        $this->children = [];
        $this->stockData = [];
        $this->stocks = [];
        $this->imageURL = '';
        $this->url = '';
        $this->configurable = $configurable;
        $this->grouped = $grouped;
        $this->bundle = $bundle;
        $this->checkSourceItemSupport = $checkSourceItemSupport;
        $this->parents = [
            self::CONFIGURABLE => [],
            self::GROUPED => [],
            self::BUNDLE => [],
        ];
        $this->linkRepository = $linkRepository;
        $this->links = [];
        $this->serializer = $serializer;
    }

    /**
     * @param int $id
     * @param int $storeID
     * @return bool
     */
    public function loadById(int $id, int $storeID)
    {
        try {
            /** @var Product $product */
            $product = $this->productRepository->getById($id);
            return $this->load($product, $storeID);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e, sprintf("Product ID %d could not be found.", $id));
            return false;
        }
    }

    /**
     * @param string $sku
     * @param int $storeID
     * @return bool
     */
    public function loadBySKU(string $sku, int $storeID)
    {
        try {
            /** @var Product $product */
            $product = $this->productRepository->get($sku);
            return $this->load($product, $storeID);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e, sprintf("Product SKU %s could not be found.", $sku));
            return false;
        }
    }

    /**
     * @param Product|ProductInterface $product
     * @param int $storeID
     * @return bool
     */
    public function load($product, int $storeID)
    {
        $this->product = $product;
        $this->product->setStoreId($storeID);
        switch ($product->getTypeId()) {
            case Configurable::TYPE_CODE:
            case Grouped::TYPE_CODE:
            case "bundle":
                $childrenIDs = $product->getTypeInstance()->getChildrenIds($this->product->getId());
                foreach ($childrenIDs as $idGroup) {
                    foreach ($idGroup as $productId) {
                        $this->children[] = To::int($productId);
                    }
                }
                break;
            case "simple":
            case "virtual":
                $this->parents = $this->getParentIds();
                break;
            case "downloadable":
                $this->links = $this->linkRepository->getLinksByProduct($product);
                $this->parents = $this->getParentIds();
                break;
        }
        $this->loadStockData();
        $this->loadURLs();
        return true;
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
            'is_visible' => $this->product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE,
            'parents' => $this->parents,
            'short_description' => $this->product->getShortDescription() ?? '',
            'description' => $this->product->getDescription() ?? '',
            'is_option_required' => To::int($this->product->getData('required_options')) > 0,
            'currency_code' => $this->product->getStore()->getCurrentCurrencyCode(),
            self::LINKS => [],
            self::CHILDREN => $this->children,
        ];
        $categoryIDs = [];
        foreach ($this->product->getCategoryIds() as $categoryId) {
            $categoryIDs[] = To::int($categoryId);
        }

        $fields['category_ids'] = $categoryIDs;

        foreach ($this->links as $link) {
            $fields[self::LINKS][] = [
                'title' => $link->getTitle() ?? '',
                'downloads' => To::int($link->getNumberOfDownloads()),
                'type' => $link->getLinkType(),
                'url' => $link->getLinkUrl(),
                'file' => $link->getLinkFile() ?? '',
                'sample_type' => $link->getSampleType(),
                'sample_url' => $link->getSampleUrl(),
                'sample_file' => $link->getSampleFile() ?? '',
                'price' => To::float($link->getPrice()),
            ];
        }

        return $fields;
    }

    /**
     * @return string|bool
     */
    public function toJSON()
    {
        return $this->serializer->serializeJson($this->toArray());
    }

    private function loadURLs()
    {
        $image = $this->product->getImage();
        if (!empty($image) && $image != self::NO_SELECT) {
            $this->imageURL = $this->resolveProductImageURL($this->product);
        }
        $this->url = $this->product->getUrlModel()->getUrlInStore(
            $this->product,
            ['_escape' => true]
        );
    }

    private function loadStockData()
    {
        $total = 0.0;
        $sku = $this->product->getSku();
        $typeId = $this->product->getTypeId();
        if ($this->checkSourceItemSupport->execute($typeId)) {
            $salableItems = $this->salableQty->execute($sku);
            foreach ($salableItems as $salable) {
                $quantity = To::float($salable['qty']);
                $total += $quantity;
                $this->stocks[] = [
                    'name' => $salable['stock_name'],
                    'quantity' => $quantity,
                    'is_manage' => To::bool($salable['manage_stock']),
                ];
            }
        } else {
            $this->logger->info(
                "Product does not support source item",
                ['sku' => $sku, 'type' => $typeId]
            );
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

    private function getParentIds(): array
    {
        $productId = $this->product->getId();
        return [
            self::CONFIGURABLE => $this->configurable->getParentIdsByChild($productId) ?? [],
            self::GROUPED => $this->grouped->getParentIdsByChild($productId) ?? [],
            self::BUNDLE => $this->bundle->getParentIdsByChild($productId) ?? [],
        ];
    }
}
