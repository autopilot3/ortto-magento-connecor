<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoRefundItemInterface;
use Ortto\Connector\Helper\To;

class OrttoRefundItem extends DataObject implements OrttoRefundItemInterface
{
    /** @inheirtDoc */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /** @inheirtDoc */
    public function getId()
    {
        return To::int($this->getData(self::ID));
    }

    /** @inheirtDoc */
    public function setOrderItemId($orderItemId)
    {
        return $this->setData(self::ORDER_ITEM_ID, $orderItemId);
    }

    /** @inheirtDoc */
    public function getOrderItemId()
    {
        return To::int($this->getData(self::ORDER_ITEM_ID));
    }

    /** @inheirtDoc */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /** @inheirtDoc */
    public function getProductId()
    {
        return To::int($this->getData(self::PRODUCT_ID));
    }

    /** @inheirtDoc */
    public function setVariantProductId($variantProductId)
    {
        return $this->setData(self::VARIANT_PRODUCT_ID, $variantProductId);
    }

    /** @inheirtDoc */
    public function getVariantProductId()
    {
        return To::int($this->getData(self::VARIANT_PRODUCT_ID));
    }

    /** @inheirtDoc */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /** @inheirtDoc */
    public function getSku()
    {
        return (string)$this->getData(self::SKU);
    }

    /** @inheirtDoc */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /** @inheirtDoc */
    public function getName()
    {
        return (string)$this->getData(self::NAME);
    }

    /** @inheirtDoc */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /** @inheirtDoc */
    public function getPrice()
    {
        return To::float($this->getData(self::PRICE));
    }

    /** @inheirtDoc */
    public function setPriceInclTax($priceInclTax)
    {
        return $this->setData(self::PRICE_INCL_TAX, $priceInclTax);
    }

    /** @inheirtDoc */
    public function getPriceInclTax()
    {
        return To::float($this->getData(self::PRICE_INCL_TAX));
    }

    /** @inheirtDoc */
    public function setBasePrice($basePrice)
    {
        return $this->setData(self::BASE_PRICE, $basePrice);
    }

    /** @inheirtDoc */
    public function getBasePrice()
    {
        return To::float($this->getData(self::BASE_PRICE));
    }

    /** @inheirtDoc */
    public function setBasePriceInclTax($basePriceInclTax)
    {
        return $this->setData(self::BASE_PRICE_INCL_TAX, $basePriceInclTax);
    }

    /** @inheirtDoc */
    public function getBasePriceInclTax()
    {
        return To::float($this->getData(self::BASE_PRICE_INCL_TAX));
    }

    /** @inheirtDoc */
    public function setQuantity($quantity)
    {
        return $this->setData(self::QUANTITY, $quantity);
    }

    /** @inheirtDoc */
    public function getQuantity()
    {
        return To::float($this->getData(self::QUANTITY));
    }

    /** @inheirtDoc */
    public function setTax($tax)
    {
        return $this->setData(self::TAX, $tax);
    }

    /** @inheirtDoc */
    public function getTax()
    {
        return To::float($this->getData(self::TAX));
    }

    /** @inheirtDoc */
    public function setBaseTax($baseTax)
    {
        return $this->setData(self::BASE_TAX, $baseTax);
    }

    /** @inheirtDoc */
    public function getBaseTax()
    {
        return To::float($this->getData(self::BASE_TAX));
    }

    /** @inheirtDoc */
    public function setTotal($total)
    {
        return $this->setData(self::TOTAL, $total);
    }

    /** @inheirtDoc */
    public function getTotal()
    {
        return To::float($this->getData(self::TOTAL));
    }

    /** @inheirtDoc */
    public function setBaseTotal($baseTotal)
    {
        return $this->setData(self::BASE_TOTAL, $baseTotal);
    }

    /** @inheirtDoc */
    public function getBaseTotal()
    {
        return To::float($this->getData(self::BASE_TOTAL));
    }

    /** @inheirtDoc */
    public function setTotalInclTax($totalInclTax)
    {
        return $this->setData(self::TOTAL_INCL_TAX, $totalInclTax);
    }

    /** @inheirtDoc */
    public function getTotalInclTax()
    {
        return To::float($this->getData(self::TOTAL_INCL_TAX));
    }

    /** @inheirtDoc */
    public function setBaseTotalInclTax($baseTotalInclTax)
    {
        return $this->setData(self::BASE_TOTAL_INCL_TAX, $baseTotalInclTax);
    }

    /** @inheirtDoc */
    public function getBaseTotalInclTax()
    {
        return To::float($this->getData(self::BASE_TOTAL_INCL_TAX));
    }

    /** @inheirtDoc */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /** @inheirtDoc */
    public function getDescription()
    {
        return (string)$this->getData(self::DESCRIPTION);
    }

    /** @inheirtDoc */
    public function setDiscount($discount)
    {
        return $this->setData(self::DISCOUNT, $discount);
    }

    /** @inheirtDoc */
    public function getDiscount()
    {
        return To::float($this->getData(self::DISCOUNT));
    }

    /** @inheirtDoc */
    public function setBaseDiscount($baseDiscount)
    {
        return $this->setData(self::BASE_DISCOUNT, $baseDiscount);
    }

    /** @inheirtDoc */
    public function getBaseDiscount()
    {
        return To::float($this->getData(self::BASE_DISCOUNT));
    }
}
