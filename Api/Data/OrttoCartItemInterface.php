<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoCartItemInterface
{
    const PRODUCT = 'product';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DISCOUNT = 'discount';
    const DISCOUNT_TAX_COMPENSATION = 'discount_tax_compensation';
    const DISCOUNT_CALCULATED = 'discount_calculated';
    const BASE_DISCOUNT = 'base_discount';
    const BASE_DISCOUNT_TAX_COMPENSATION = 'base_discount_tax_compensation';
    const BASE_DISCOUNT_CALCULATED = 'base_discount_calculated';
    const BASE_PRICE = 'base_price';
    const BASE_PRICE_INCL_TAX = 'base_price_incl_tax';
    const PRICE = 'price';
    const PRICE_INCL_TAX = 'price_incl_tax';
    const BASE_ROW_TOTAL = 'base_row_total';
    const BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';
    const ROW_TOTAL = 'row_total';
    const ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';
    const ROW_TOTAL_AFTER_DISCOUNT = 'row_total_after_discount';
    const BASE_TAX = 'base_tax';
    const BASE_TAX_BEFORE_DISCOUNT = 'base_tax_before_discount';
    const TAX = 'tax';
    const TAX_BEFORE_DISCOUNT = 'tax_before_discount';
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
    * Set discount tax compensation
    *
    * @param float $discountTaxCompensation
    * @return $this
    */
    public function setDiscountTaxCompensation($discountTaxCompensation);

    /**
    * Get discount tax compensation
    *
    * @return float
    */
    public function getDiscountTaxCompensation();
    /**
    * Set discount calculated
    *
    * @param float $discountCalculated
    * @return $this
    */
    public function setDiscountCalculated($discountCalculated);

    /**
    * Get discount calculated
    *
    * @return float
    */
    public function getDiscountCalculated();
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
    * Set base discount tax compensation
    *
    * @param float $baseDiscountTaxCompensation
    * @return $this
    */
    public function setBaseDiscountTaxCompensation($baseDiscountTaxCompensation);

    /**
    * Get base discount tax compensation
    *
    * @return float
    */
    public function getBaseDiscountTaxCompensation();
    /**
    * Set base discount calculated
    *
    * @param float $baseDiscountCalculated
    * @return $this
    */
    public function setBaseDiscountCalculated($baseDiscountCalculated);

    /**
    * Get base discount calculated
    *
    * @return float
    */
    public function getBaseDiscountCalculated();
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
    * Set base price incl tax
    *
    * @param float $basePriceInclTax
    * @return $this
    */
    public function setBasePriceInclTax($basePriceInclTax);

    /**
    * Get base price incl tax
    *
    * @return float
    */
    public function getBasePriceInclTax();
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
    * Set price incl tax
    *
    * @param float $priceInclTax
    * @return $this
    */
    public function setPriceInclTax($priceInclTax);

    /**
    * Get price incl tax
    *
    * @return float
    */
    public function getPriceInclTax();
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
    * Set base row total incl tax
    *
    * @param float $baseRowTotalInclTax
    * @return $this
    */
    public function setBaseRowTotalInclTax($baseRowTotalInclTax);

    /**
    * Get base row total incl tax
    *
    * @return float
    */
    public function getBaseRowTotalInclTax();
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
    * Set row total incl tax
    *
    * @param float $rowTotalInclTax
    * @return $this
    */
    public function setRowTotalInclTax($rowTotalInclTax);

    /**
    * Get row total incl tax
    *
    * @return float
    */
    public function getRowTotalInclTax();
    /**
    * Set row total after discount
    *
    * @param float $rowTotalAfterDiscount
    * @return $this
    */
    public function setRowTotalAfterDiscount($rowTotalAfterDiscount);

    /**
    * Get row total after discount
    *
    * @return float
    */
    public function getRowTotalAfterDiscount();
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
    * Set base tax before discount
    *
    * @param float $baseTaxBeforeDiscount
    * @return $this
    */
    public function setBaseTaxBeforeDiscount($baseTaxBeforeDiscount);

    /**
    * Get base tax before discount
    *
    * @return float
    */
    public function getBaseTaxBeforeDiscount();
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
    * Set tax before discount
    *
    * @param float $taxBeforeDiscount
    * @return $this
    */
    public function setTaxBeforeDiscount($taxBeforeDiscount);

    /**
    * Get tax before discount
    *
    * @return float
    */
    public function getTaxBeforeDiscount();
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
}
