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
        return To::float($this->getData(self::DISCOUNT));
    }

    /** @inheirtDoc */
    public function setDiscountTaxCompensation($discountTaxCompensation)
    {
        return $this->setData(self::DISCOUNT_TAX_COMPENSATION, $discountTaxCompensation);
    }

    /** @inheirtDoc */
    public function getDiscountTaxCompensation()
    {
        return To::float($this->getData(self::DISCOUNT_TAX_COMPENSATION));
    }

    /** @inheirtDoc */
    public function setDiscountCalculated($discountCalculated)
    {
        return $this->setData(self::DISCOUNT_CALCULATED, $discountCalculated);
    }

    /** @inheirtDoc */
    public function getDiscountCalculated()
    {
        return To::float($this->getData(self::DISCOUNT_CALCULATED));
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

    /** @inheirtDoc */
    public function setBaseDiscountTaxCompensation($baseDiscountTaxCompensation)
    {
        return $this->setData(self::BASE_DISCOUNT_TAX_COMPENSATION, $baseDiscountTaxCompensation);
    }

    /** @inheirtDoc */
    public function getBaseDiscountTaxCompensation()
    {
        return To::float($this->getData(self::BASE_DISCOUNT_TAX_COMPENSATION));
    }

    /** @inheirtDoc */
    public function setBaseDiscountCalculated($baseDiscountCalculated)
    {
        return $this->setData(self::BASE_DISCOUNT_CALCULATED, $baseDiscountCalculated);
    }

    /** @inheirtDoc */
    public function getBaseDiscountCalculated()
    {
        return To::float($this->getData(self::BASE_DISCOUNT_CALCULATED));
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
    public function setBaseRowTotal($baseRowTotal)
    {
        return $this->setData(self::BASE_ROW_TOTAL, $baseRowTotal);
    }

    /** @inheirtDoc */
    public function getBaseRowTotal()
    {
        return To::float($this->getData(self::BASE_ROW_TOTAL));
    }

    /** @inheirtDoc */
    public function setBaseRowTotalInclTax($baseRowTotalInclTax)
    {
        return $this->setData(self::BASE_ROW_TOTAL_INCL_TAX, $baseRowTotalInclTax);
    }

    /** @inheirtDoc */
    public function getBaseRowTotalInclTax()
    {
        return To::float($this->getData(self::BASE_ROW_TOTAL_INCL_TAX));
    }

    /** @inheirtDoc */
    public function setRowTotal($rowTotal)
    {
        return $this->setData(self::ROW_TOTAL, $rowTotal);
    }

    /** @inheirtDoc */
    public function getRowTotal()
    {
        return To::float($this->getData(self::ROW_TOTAL));
    }

    /** @inheirtDoc */
    public function setRowTotalInclTax($rowTotalInclTax)
    {
        return $this->setData(self::ROW_TOTAL_INCL_TAX, $rowTotalInclTax);
    }

    /** @inheirtDoc */
    public function getRowTotalInclTax()
    {
        return To::float($this->getData(self::ROW_TOTAL_INCL_TAX));
    }

    /** @inheirtDoc */
    public function setRowTotalAfterDiscount($rowTotalAfterDiscount)
    {
        return $this->setData(self::ROW_TOTAL_AFTER_DISCOUNT, $rowTotalAfterDiscount);
    }

    /** @inheirtDoc */
    public function getRowTotalAfterDiscount()
    {
        return To::float($this->getData(self::ROW_TOTAL_AFTER_DISCOUNT));
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
    public function setBaseTaxBeforeDiscount($baseTaxBeforeDiscount)
    {
        return $this->setData(self::BASE_TAX_BEFORE_DISCOUNT, $baseTaxBeforeDiscount);
    }

    /** @inheirtDoc */
    public function getBaseTaxBeforeDiscount()
    {
        return To::float($this->getData(self::BASE_TAX_BEFORE_DISCOUNT));
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
    public function setTaxBeforeDiscount($taxBeforeDiscount)
    {
        return $this->setData(self::TAX_BEFORE_DISCOUNT, $taxBeforeDiscount);
    }

    /** @inheirtDoc */
    public function getTaxBeforeDiscount()
    {
        return To::float($this->getData(self::TAX_BEFORE_DISCOUNT));
    }

    /** @inheirtDoc */
    public function setTaxPercent($taxPercent)
    {
        return $this->setData(self::TAX_PERCENT, $taxPercent);
    }

    /** @inheirtDoc */
    public function getTaxPercent()
    {
        return To::float($this->getData(self::TAX_PERCENT));
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
}
