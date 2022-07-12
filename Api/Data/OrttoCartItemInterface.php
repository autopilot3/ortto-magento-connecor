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
    * @param int $discount
    * @return $this
    */
    public function setDiscount($discount);

    /**
    * Get discount
    *
    * @return int
    */
    public function getDiscount();

    /**
    * Set base discount
    *
    * @param int $baseDiscount
    * @return $this
    */
    public function setBaseDiscount($baseDiscount);

    /**
    * Get base discount
    *
    * @return int
    */
    public function getBaseDiscount();

    /**
    * Set price
    *
    * @param int $price
    * @return $this
    */
    public function setPrice($price);

    /**
    * Get price
    *
    * @return int
    */
    public function getPrice();

    /**
    * Set base price
    *
    * @param int $basePrice
    * @return $this
    */
    public function setBasePrice($basePrice);

    /**
    * Get base price
    *
    * @return int
    */
    public function getBasePrice();

    /**
    * Set row total
    *
    * @param int $rowTotal
    * @return $this
    */
    public function setRowTotal($rowTotal);

    /**
    * Get row total
    *
    * @return int
    */
    public function getRowTotal();

    /**
    * Set base row total
    *
    * @param int $baseRowTotal
    * @return $this
    */
    public function setBaseRowTotal($baseRowTotal);

    /**
    * Get base row total
    *
    * @return int
    */
    public function getBaseRowTotal();

    /**
    * Set tax
    *
    * @param int $tax
    * @return $this
    */
    public function setTax($tax);

    /**
    * Get tax
    *
    * @return int
    */
    public function getTax();

    /**
    * Set base tax
    *
    * @param int $baseTax
    * @return $this
    */
    public function setBaseTax($baseTax);

    /**
    * Get base tax
    *
    * @return int
    */
    public function getBaseTax();

    /**
    * Set tax percent
    *
    * @param int $taxPercent
    * @return $this
    */
    public function setTaxPercent($taxPercent);

    /**
    * Get tax percent
    *
    * @return int
    */
    public function getTaxPercent();

    /**
    * Set quantity
    *
    * @param int $quantity
    * @return $this
    */
    public function setQuantity($quantity);

    /**
    * Get quantity
    *
    * @return int
    */
    public function getQuantity();

    /**
    * Convert object data to array
    *
    * @return array
    */
    public function serializeToArray();
}
