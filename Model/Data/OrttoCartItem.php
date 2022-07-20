<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoCartItemInterface;
use Ortto\Connector\Helper\To;

class OrttoCartItem extends DataObject implements OrttoCartItemInterface
{
    /** @inheirtDoc */
    public function setProduct($product)
    {
        return $this->setData(self::PRODUCT, $product);
    }

    /** @inheirtDoc */
    public function getProduct()
    {
        return $this->getData(self::PRODUCT);
    }

    /** @inheirtDoc */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /** @inheirtDoc */
    public function getCreatedAt()
    {
        return (string)$this->getData(self::CREATED_AT);
    }

    /** @inheirtDoc */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /** @inheirtDoc */
    public function getUpdatedAt()
    {
        return (string)$this->getData(self::UPDATED_AT);
    }

    /** @inheirtDoc */
    public function setDiscount($discount)
    {
        return $this->setData(self::DISCOUNT, $discount);
    }

    /** @inheirtDoc */
    public function getDiscount()
    {
        return To::int($this->getData(self::DISCOUNT));
    }

    /** @inheirtDoc */
    public function setBaseDiscount($baseDiscount)
    {
        return $this->setData(self::BASE_DISCOUNT, $baseDiscount);
    }

    /** @inheirtDoc */
    public function getBaseDiscount()
    {
        return To::int($this->getData(self::BASE_DISCOUNT));
    }

    /** @inheirtDoc */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /** @inheirtDoc */
    public function getPrice()
    {
        return To::int($this->getData(self::PRICE));
    }

    /** @inheirtDoc */
    public function setBasePrice($basePrice)
    {
        return $this->setData(self::BASE_PRICE, $basePrice);
    }

    /** @inheirtDoc */
    public function getBasePrice()
    {
        return To::int($this->getData(self::BASE_PRICE));
    }

    /** @inheirtDoc */
    public function setRowTotal($rowTotal)
    {
        return $this->setData(self::ROW_TOTAL, $rowTotal);
    }

    /** @inheirtDoc */
    public function getRowTotal()
    {
        return To::int($this->getData(self::ROW_TOTAL));
    }

    /** @inheirtDoc */
    public function setBaseRowTotal($baseRowTotal)
    {
        return $this->setData(self::BASE_ROW_TOTAL, $baseRowTotal);
    }

    /** @inheirtDoc */
    public function getBaseRowTotal()
    {
        return To::int($this->getData(self::BASE_ROW_TOTAL));
    }

    /** @inheirtDoc */
    public function setTax($tax)
    {
        return $this->setData(self::TAX, $tax);
    }

    /** @inheirtDoc */
    public function getTax()
    {
        return To::int($this->getData(self::TAX));
    }

    /** @inheirtDoc */
    public function setBaseTax($baseTax)
    {
        return $this->setData(self::BASE_TAX, $baseTax);
    }

    /** @inheirtDoc */
    public function getBaseTax()
    {
        return To::int($this->getData(self::BASE_TAX));
    }

    /** @inheirtDoc */
    public function setTaxPercent($taxPercent)
    {
        return $this->setData(self::TAX_PERCENT, $taxPercent);
    }

    /** @inheirtDoc */
    public function getTaxPercent()
    {
        return To::int($this->getData(self::TAX_PERCENT));
    }

    /** @inheirtDoc */
    public function setQuantity($quantity)
    {
        return $this->setData(self::QUANTITY, $quantity);
    }

    /** @inheirtDoc */
    public function getQuantity()
    {
        return To::int($this->getData(self::QUANTITY));
    }

    /** @inheirtDoc */
    public function setVariant($variant)
    {
        return $this->setData(self::VARIANT, $variant);
    }

    /** @inheirtDoc */
    public function getVariant()
    {
        return $this->getData(self::VARIANT);
    }

    /** @inheirtDoc */
    public function serializeToArray()
    {
        if ($this == null) {
            return null;
        }
        $result=[];
        $product = $this->getProduct();
        $result[self::PRODUCT] = $product != null ? $product->serializeToArray() : null;
        $result[self::CREATED_AT] = $this->getCreatedAt();
        $result[self::UPDATED_AT] = $this->getUpdatedAt();
        $result[self::DISCOUNT] = $this->getDiscount();
        $result[self::BASE_DISCOUNT] = $this->getBaseDiscount();
        $result[self::PRICE] = $this->getPrice();
        $result[self::BASE_PRICE] = $this->getBasePrice();
        $result[self::ROW_TOTAL] = $this->getRowTotal();
        $result[self::BASE_ROW_TOTAL] = $this->getBaseRowTotal();
        $result[self::TAX] = $this->getTax();
        $result[self::BASE_TAX] = $this->getBaseTax();
        $result[self::TAX_PERCENT] = $this->getTaxPercent();
        $result[self::QUANTITY] = $this->getQuantity();
        $variant = $this->getVariant();
        $result[self::VARIANT] = $variant != null ? $variant->serializeToArray() : null;
        return $result;
    }
}
