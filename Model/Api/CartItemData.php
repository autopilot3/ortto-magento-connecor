<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Api;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Ortto\Connector\Helper\To;
use Magento\Framework\Serialize\JsonConverter;
use Magento\Quote\Model\Quote;
use Ortto\Connector\Logger\OrttoLoggerInterface;

class CartItemData
{
    private ProductDataFactory $productDataFactory;

    private Quote\Item $item;
    private OrttoLoggerInterface $logger;

    public function __construct(
        ProductDataFactory $productDataFactory,
        OrttoLoggerInterface $logger
    ) {
        $this->productDataFactory = $productDataFactory;
        $this->logger = $logger;
    }

    /**
     * @param Quote\Item $item
     * @return void
     */
    public function load(Quote\Item $item)
    {
        $this->item = $item;
    }

    /**
     * @return string|bool
     */
    public function toJSON()
    {
        return JsonConverter::convert($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if (empty($this->item)) {
            return [];
        }
        $cartProduct = $this->item->getProduct();
        $storeId = To::int($this->item->getStoreId());
        $product = $this->productDataFactory->create();
        if (!$product->load($cartProduct, $storeId)) {
            return [];
        }
        $fields = [
            'base_discount' => To::float($this->item->getBaseDiscountAmount()),
            'base_discount_tax_compensation' => To::float($this->item->getBaseDiscountTaxCompensationAmount()),
            'base_discount_calculated' => To::float($this->item->getBaseDiscountCalculationPrice()),
            'discount' => To::float($this->item->getDiscountAmount()),
            'discount_calculated' => To::float($this->item->getDiscountCalculationPrice()),
            'discount_tax_compensation' => To::float($this->item->getDiscountTaxCompensationAmount()),
            'base_price' => To::float($this->item->getBasePrice()),
            'base_price_incl_tax' => To::float($this->item->getBasePriceInclTax()),
            'price' => To::float($this->item->getPrice()),
            'price_incl_tax' => To::float($this->item->getPriceInclTax()),
            'base_row_total' => To::float($this->item->getBaseRowTotal()),
            'base_row_total_incl_tax' => To::float($this->item->getBaseRowTotalInclTax()),
            'row_total' => To::float($this->item->getRowTotal()),
            'row_total_incl_tax' => To::float($this->item->getRowTotalInclTax()),
            'row_total_after_discount' => To::float($this->item->getRowTotalWithDiscount()),
            'base_tax' => To::float($this->item->getBaseTaxAmount()),
            'base_tax_before_discount' => To::float($this->item->getBaseTaxBeforeDiscount()),
            'tax' => To::float($this->item->getTaxAmount()),
            'tax_before_discount' => To::float($this->item->getTaxBeforeDiscount()),
            'tax_percent' => To::float($this->item->getTaxPercent()),
            'quantity' => To::float($this->item->getQty()),
            'product' => $product->toArray(),
        ];
        if ($this->item->getProductType() == Configurable::TYPE_CODE &&
            $variant = $this->getVariant((string)$cartProduct->getSku(), $storeId)) {
            $fields['variant'] = $variant;
        }
        return $fields;
    }

    /**
     * @param string $sku
     * @param int $storeId
     * @return array|null
     */
    private function getVariant(string $sku, int $storeId): ?array
    {
        // The SKU of a configurable product's quote is set to the variant's SKU
        $variant = $this->productDataFactory->create();
        if ($variant->loadBySKU($sku, $storeId)) {
            return $variant->toArray();
        }
        $this->logger->warn(
            "No variant was found for the specified SKU",
            ['sku' => $sku, 'store_id' => $storeId, 'quote_id' => $this->item->getId()]
        );
        return null;
    }
}
