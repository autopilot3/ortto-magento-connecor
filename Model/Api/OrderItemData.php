<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Model\Api;

use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Helper\To;
use Magento\Sales\Api\Data\OrderItemInterface;

class OrderItemData
{
    private Data $helper;
    private ProductDataFactory $productDataFactory;

    public function __construct(Data $helper, ProductDataFactory $productDataFactory)
    {
        $this->helper = $helper;
        $this->productDataFactory = $productDataFactory;
    }

    /**
     * @param OrderItemInterface[] $items
     * @return array
     */
    public function toArray(array $items): array
    {
        if (empty($items)) {
            return [];
        }
        $result = [];
        foreach ($items as $item) {
            $product = $this->productDataFactory->create();
            if (!$product->loadById(To::int($item->getProductId()))) {
                continue;
            }
            $result[] = [
                'id' => To::int($item->getItemId()),
                'is_virtual' => To::bool($item->getIsVirtual()),
                'name' => (string)$item->getName(),
                'sku' => (string)$item->getSku(),
                'description' => (string)$item->getDescription(),
                'created_at' => $this->helper->toUTC($item->getCreatedAt()),
                'updated_at' => $this->helper->toUTC($item->getUpdatedAt()),
                'refunded' => To::float($item->getAmountRefunded()),
                'base_refunded' => To::float($item->getBaseAmountRefunded()),
                'base_cost' => To::float($item->getBaseCost()),
                'discount' => To::float($item->getDiscountAmount()),
                'base_discount' => To::float($item->getBaseDiscountAmount()),
                'discount_percent' => To::float($item->getDiscountPercent()),
                'discount_invoiced' => To::float($item->getDiscountInvoiced()),
                'base_discount_invoiced' => To::float($item->getBaseDiscountInvoiced()),
                'discount_refunded' => To::float($item->getDiscountRefunded()),
                'base_discount_refunded' => To::float($item->getBaseDiscountRefunded()),
                'total' => To::float($item->getRowTotal()),
                'base_total' => To::float($item->getBaseRowTotal()),
                'total_incl_tax' => To::float($item->getRowTotalInclTax()),
                'base_total_incl_tax' => To::float($item->getBaseRowTotalInclTax()),
                'price' => To::float($item->getPrice()),
                'base_price' => To::float($item->getBasePrice()),
                'original_price' => To::float($item->getOriginalPrice()),
                'base_original_price' => To::float($item->getBaseOriginalPrice()),
                'qty_ordered' => To::float($item->getQtyOrdered()),
                'qty_back_ordered' => To::float($item->getQtyBackordered()),
                'qty_refunded' => To::float($item->getQtyRefunded()),
                'qty_returned' => To::float($item->getQtyReturned()),
                'qty_cancelled' => To::float($item->getQtyCanceled()),
                'qty_shipped' => To::float($item->getQtyShipped()),
                'gty_invoiced' => To::float($item->getQtyInvoiced()),
                'tax' => To::float($item->getTaxAmount()),
                'base_tax' => To::float($item->getBaseTaxAmount()),
                'is_free_shipping' => $item->getFreeShipping(),
                'tax_percent' => To::float($item->getTaxPercent()),
                'additional_data' => (string)$item->getAdditionalData(),
                'store_id' => To::int($item->getStoreId()),
                'product' => $product->toArray(),
            ];
        }
        return $result;
    }
}
