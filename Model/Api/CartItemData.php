<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Api;

use Ortto\Connector\Api\OrttoSerializerInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Magento\Quote\Model\Quote;
use Ortto\Connector\Logger\OrttoLoggerInterface;

class CartItemData
{
    private ProductDataFactory $productDataFactory;

    private Quote\Item $item;
    private OrttoLoggerInterface $logger;
    private OrttoSerializerInterface $serializer;
    private Data $helper;

    public function __construct(
        ProductDataFactory $productDataFactory,
        OrttoLoggerInterface $logger,
        OrttoSerializerInterface $serializer,
        Data $helper
    ) {
        $this->productDataFactory = $productDataFactory;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->helper = $helper;
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
        return $this->serializer->serializeJson($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if (empty($this->item)) {
            return [];
        }
        $storeId = To::int($this->item->getStoreId());
        $product = $this->productDataFactory->create();
        if (!$product->loadById(To::int($this->item->getProduct()->getId()), $storeId)) {
            return [];
        }
        return [
            'created_at' => $this->helper->toUTC($this->item->getCreatedAt()),
            'updated_at' => $this->helper->toUTC($this->item->getUpdatedAt()),
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
    }
}
