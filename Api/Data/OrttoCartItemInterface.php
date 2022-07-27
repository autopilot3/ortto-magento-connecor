<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoCartItemInterface
{
    const PRODUCT = 'product';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DISCOUNT = 'discount';
    const BASE_DISCOUNT = 'base_discount';
    const PRICE = 'price';
    const BASE_PRICE = 'base_price';
    const ROW_TOTAL = 'row_total';
    const BASE_ROW_TOTAL = 'base_row_total';
    const TAX = 'tax';
    const BASE_TAX = 'base_tax';
    const TAX_PERCENT = 'tax_percent';
    const QUANTITY = 'quantity';
    const VARIANT = 'variant';

    /**
    * Set product
    *
    * @param \Ortto\Connector\Api\Data\OrttoProductInterface $product
    * @return $this
    */
    public function setProduct($product);

    /**
    * Get product
    *
    * @return \Ortto\Connector\Api\Data\OrttoProductInterface
    */
    public function getProduct();

    /**
    * Set created at
    *
    * @param string $createdAt
    * @return $this
    */
    public function setCreatedAt($createdAt);

    /**
    * Get created at
    *
    * @return string
    */
    public function getCreatedAt();

    /**
    * Set updated at
    *
    * @param string $updatedAt
    * @return $this
    */
    public function setUpdatedAt($updatedAt);

    /**
    * Get updated at
    *
    * @return string
    */
    public function getUpdatedAt();

    /**
    * Set discount
    *
    * @param float $discount
    * @return $this
    */
    public function setDiscount($discount);

    /**
    * Get discount
    *
    * @return float
    */
    public function getDiscount();

    /**
    * Set base discount
    *
    * @param float $baseDiscount
    * @return $this
    */
    public function setBaseDiscount($baseDiscount);

    /**
    * Get base discount
    *
    * @return float
    */
    public function getBaseDiscount();

    /**
    * Set price
    *
    * @param float $price
    * @return $this
    */
    public function setPrice($price);

    /**
    * Get price
    *
    * @return float
    */
    public function getPrice();

    /**
    * Set base price
    *
    * @param float $basePrice
    * @return $this
    */
    public function setBasePrice($basePrice);

    /**
    * Get base price
    *
    * @return float
    */
    public function getBasePrice();

    /**
    * Set row total
    *
    * @param float $rowTotal
    * @return $this
    */
    public function setRowTotal($rowTotal);

    /**
    * Get row total
    *
    * @return float
    */
    public function getRowTotal();

    /**
    * Set base row total
    *
    * @param float $baseRowTotal
    * @return $this
    */
    public function setBaseRowTotal($baseRowTotal);

    /**
    * Get base row total
    *
    * @return float
    */
    public function getBaseRowTotal();

    /**
    * Set tax
    *
    * @param float $tax
    * @return $this
    */
    public function setTax($tax);

    /**
    * Get tax
    *
    * @return float
    */
    public function getTax();

    /**
    * Set base tax
    *
    * @param float $baseTax
    * @return $this
    */
    public function setBaseTax($baseTax);

    /**
    * Get base tax
    *
    * @return float
    */
    public function getBaseTax();

    /**
    * Set tax percent
    *
    * @param float $taxPercent
    * @return $this
    */
    public function setTaxPercent($taxPercent);

    /**
    * Get tax percent
    *
    * @return float
    */
    public function getTaxPercent();

    /**
    * Set quantity
    *
    * @param float $quantity
    * @return $this
    */
    public function setQuantity($quantity);

    /**
    * Get quantity
    *
    * @return float
    */
    public function getQuantity();

    /**
    * Set variant
    *
    * @param \Ortto\Connector\Api\Data\OrttoProductInterface|null $variant
    * @return $this
    */
    public function setVariant($variant);

    /**
    * Get variant
    *
    * @return \Ortto\Connector\Api\Data\OrttoProductInterface|null
    */
    public function getVariant();

    /**
    * Convert object data to array
    *
    * @return array
    */
    public function serializeToArray();
}
