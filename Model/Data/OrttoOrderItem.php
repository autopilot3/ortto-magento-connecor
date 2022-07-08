<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoOrderItemInterface;
use Ortto\Connector\Helper\To;

class OrttoOrderItem extends DataObject implements OrttoOrderItemInterface
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
    public function setIsVirtual($isVirtual)
    {
        return $this->setData(self::IS_VIRTUAL, $isVirtual);
    }

    /** @inheirtDoc */
    public function getIsVirtual()
    {
        return To::bool($this->getData(self::IS_VIRTUAL));
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
    public function setProductImage($productImage)
    {
        return $this->setData(self::PRODUCT_IMAGE, $productImage);
    }

    /** @inheirtDoc */
    public function getProductImage()
    {
        return (string)$this->getData(self::PRODUCT_IMAGE);
    }

    /** @inheirtDoc */
    public function setProductUrl($productUrl)
    {
        return $this->setData(self::PRODUCT_URL, $productUrl);
    }

    /** @inheirtDoc */
    public function getProductUrl()
    {
        return (string)$this->getData(self::PRODUCT_URL);
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
    public function setRefunded($refunded)
    {
        return $this->setData(self::REFUNDED, $refunded);
    }

    /** @inheirtDoc */
    public function getRefunded()
    {
        return To::float($this->getData(self::REFUNDED));
    }

    /** @inheirtDoc */
    public function setBaseRefunded($baseRefunded)
    {
        return $this->setData(self::BASE_REFUNDED, $baseRefunded);
    }

    /** @inheirtDoc */
    public function getBaseRefunded()
    {
        return To::float($this->getData(self::BASE_REFUNDED));
    }

    /** @inheirtDoc */
    public function setBaseCost($baseCost)
    {
        return $this->setData(self::BASE_COST, $baseCost);
    }

    /** @inheirtDoc */
    public function getBaseCost()
    {
        return To::float($this->getData(self::BASE_COST));
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
    public function setDiscountPercent($discountPercent)
    {
        return $this->setData(self::DISCOUNT_PERCENT, $discountPercent);
    }

    /** @inheirtDoc */
    public function getDiscountPercent()
    {
        return To::float($this->getData(self::DISCOUNT_PERCENT));
    }

    /** @inheirtDoc */
    public function setDiscountInvoiced($discountInvoiced)
    {
        return $this->setData(self::DISCOUNT_INVOICED, $discountInvoiced);
    }

    /** @inheirtDoc */
    public function getDiscountInvoiced()
    {
        return To::float($this->getData(self::DISCOUNT_INVOICED));
    }

    /** @inheirtDoc */
    public function setBaseDiscountInvoiced($baseDiscountInvoiced)
    {
        return $this->setData(self::BASE_DISCOUNT_INVOICED, $baseDiscountInvoiced);
    }

    /** @inheirtDoc */
    public function getBaseDiscountInvoiced()
    {
        return To::float($this->getData(self::BASE_DISCOUNT_INVOICED));
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
    public function setDiscountRefunded($discountRefunded)
    {
        return $this->setData(self::DISCOUNT_REFUNDED, $discountRefunded);
    }

    /** @inheirtDoc */
    public function getDiscountRefunded()
    {
        return To::float($this->getData(self::DISCOUNT_REFUNDED));
    }

    /** @inheirtDoc */
    public function setBaseDiscountRefunded($baseDiscountRefunded)
    {
        return $this->setData(self::BASE_DISCOUNT_REFUNDED, $baseDiscountRefunded);
    }

    /** @inheirtDoc */
    public function getBaseDiscountRefunded()
    {
        return To::float($this->getData(self::BASE_DISCOUNT_REFUNDED));
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
    public function setOriginalPrice($originalPrice)
    {
        return $this->setData(self::ORIGINAL_PRICE, $originalPrice);
    }

    /** @inheirtDoc */
    public function getOriginalPrice()
    {
        return To::float($this->getData(self::ORIGINAL_PRICE));
    }

    /** @inheirtDoc */
    public function setBaseOriginalPrice($baseOriginalPrice)
    {
        return $this->setData(self::BASE_ORIGINAL_PRICE, $baseOriginalPrice);
    }

    /** @inheirtDoc */
    public function getBaseOriginalPrice()
    {
        return To::float($this->getData(self::BASE_ORIGINAL_PRICE));
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
    public function setQtyInvoiced($qtyInvoiced)
    {
        return $this->setData(self::QTY_INVOICED, $qtyInvoiced);
    }

    /** @inheirtDoc */
    public function getQtyInvoiced()
    {
        return To::float($this->getData(self::QTY_INVOICED));
    }

    /** @inheirtDoc */
    public function setQtyBackOrdered($qtyBackOrdered)
    {
        return $this->setData(self::QTY_BACK_ORDERED, $qtyBackOrdered);
    }

    /** @inheirtDoc */
    public function getQtyBackOrdered()
    {
        return To::float($this->getData(self::QTY_BACK_ORDERED));
    }

    /** @inheirtDoc */
    public function setQtyCancelled($qtyCancelled)
    {
        return $this->setData(self::QTY_CANCELLED, $qtyCancelled);
    }

    /** @inheirtDoc */
    public function getQtyCancelled()
    {
        return To::float($this->getData(self::QTY_CANCELLED));
    }

    /** @inheirtDoc */
    public function setQtyOrdered($qtyOrdered)
    {
        return $this->setData(self::QTY_ORDERED, $qtyOrdered);
    }

    /** @inheirtDoc */
    public function getQtyOrdered()
    {
        return To::float($this->getData(self::QTY_ORDERED));
    }

    /** @inheirtDoc */
    public function setQtyRefunded($qtyRefunded)
    {
        return $this->setData(self::QTY_REFUNDED, $qtyRefunded);
    }

    /** @inheirtDoc */
    public function getQtyRefunded()
    {
        return To::float($this->getData(self::QTY_REFUNDED));
    }

    /** @inheirtDoc */
    public function setQtyReturned($qtyReturned)
    {
        return $this->setData(self::QTY_RETURNED, $qtyReturned);
    }

    /** @inheirtDoc */
    public function getQtyReturned()
    {
        return To::float($this->getData(self::QTY_RETURNED));
    }

    /** @inheirtDoc */
    public function setQtyShipped($qtyShipped)
    {
        return $this->setData(self::QTY_SHIPPED, $qtyShipped);
    }

    /** @inheirtDoc */
    public function getQtyShipped()
    {
        return To::float($this->getData(self::QTY_SHIPPED));
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
    public function setIsFreeShipping($isFreeShipping)
    {
        return $this->setData(self::IS_FREE_SHIPPING, $isFreeShipping);
    }

    /** @inheirtDoc */
    public function getIsFreeShipping()
    {
        return To::bool($this->getData(self::IS_FREE_SHIPPING));
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
    public function setAdditionalData($additionalData)
    {
        return $this->setData(self::ADDITIONAL_DATA, $additionalData);
    }

    /** @inheirtDoc */
    public function getAdditionalData()
    {
        return (string)$this->getData(self::ADDITIONAL_DATA);
    }

    /** @inheirtDoc */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /** @inheirtDoc */
    public function getStoreId()
    {
        return To::int($this->getData(self::STORE_ID));
    }
}
