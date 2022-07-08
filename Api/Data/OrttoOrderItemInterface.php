<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoOrderItemInterface
{
    const ID = 'id';
    const IS_VIRTUAL = 'is_virtual';
    const SKU = 'sku';
    const DESCRIPTION = 'description';
    const NAME = 'name';
    const PRODUCT_ID = 'product_id';
    const PRODUCT_IMAGE = 'product_image';
    const PRODUCT_URL = 'product_url';
    const VARIANT_PRODUCT_ID = 'variant_product_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const REFUNDED = 'refunded';
    const BASE_REFUNDED = 'base_refunded';
    const BASE_COST = 'base_cost';
    const DISCOUNT = 'discount';
    const DISCOUNT_PERCENT = 'discount_percent';
    const DISCOUNT_INVOICED = 'discount_invoiced';
    const BASE_DISCOUNT_INVOICED = 'base_discount_invoiced';
    const BASE_DISCOUNT = 'base_discount';
    const DISCOUNT_REFUNDED = 'discount_refunded';
    const BASE_DISCOUNT_REFUNDED = 'base_discount_refunded';
    const PRICE = 'price';
    const BASE_PRICE = 'base_price';
    const ORIGINAL_PRICE = 'original_price';
    const BASE_ORIGINAL_PRICE = 'base_original_price';
    const TOTAL = 'total';
    const BASE_TOTAL = 'base_total';
    const TOTAL_INCL_TAX = 'total_incl_tax';
    const BASE_TOTAL_INCL_TAX = 'base_total_incl_tax';
    const QTY_INVOICED = 'qty_invoiced';
    const QTY_BACK_ORDERED = 'qty_back_ordered';
    const QTY_CANCELLED = 'qty_cancelled';
    const QTY_ORDERED = 'qty_ordered';
    const QTY_REFUNDED = 'qty_refunded';
    const QTY_RETURNED = 'qty_returned';
    const QTY_SHIPPED = 'qty_shipped';
    const TAX = 'tax';
    const BASE_TAX = 'base_tax';
    const IS_FREE_SHIPPING = 'is_free_shipping';
    const TAX_PERCENT = 'tax_percent';
    const ADDITIONAL_DATA = 'additional_data';
    const STORE_ID = 'store_id';

    /**
    * Set id
    *
    * @param int $id
    * @return $this
    */
    public function setId($id);

    /**
    * Get id
    *
    * @return int
    */
    public function getId();

    /**
    * Set is virtual
    *
    * @param bool $isVirtual
    * @return $this
    */
    public function setIsVirtual($isVirtual);

    /**
    * Get is virtual
    *
    * @return bool
    */
    public function getIsVirtual();

    /**
    * Set sku
    *
    * @param string $sku
    * @return $this
    */
    public function setSku($sku);

    /**
    * Get sku
    *
    * @return string
    */
    public function getSku();

    /**
    * Set description
    *
    * @param string $description
    * @return $this
    */
    public function setDescription($description);

    /**
    * Get description
    *
    * @return string
    */
    public function getDescription();

    /**
    * Set name
    *
    * @param string $name
    * @return $this
    */
    public function setName($name);

    /**
    * Get name
    *
    * @return string
    */
    public function getName();

    /**
    * Set product id
    *
    * @param int $productId
    * @return $this
    */
    public function setProductId($productId);

    /**
    * Get product id
    *
    * @return int
    */
    public function getProductId();

    /**
    * Set product image
    *
    * @param string $productImage
    * @return $this
    */
    public function setProductImage($productImage);

    /**
    * Get product image
    *
    * @return string
    */
    public function getProductImage();

    /**
    * Set product url
    *
    * @param string $productUrl
    * @return $this
    */
    public function setProductUrl($productUrl);

    /**
    * Get product url
    *
    * @return string
    */
    public function getProductUrl();

    /**
    * Set variant product id
    *
    * @param int|null $variantProductId
    * @return $this
    */
    public function setVariantProductId($variantProductId);

    /**
    * Get variant product id
    *
    * @return int|null
    */
    public function getVariantProductId();

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
    * Set refunded
    *
    * @param float $refunded
    * @return $this
    */
    public function setRefunded($refunded);

    /**
    * Get refunded
    *
    * @return float
    */
    public function getRefunded();

    /**
    * Set base refunded
    *
    * @param float $baseRefunded
    * @return $this
    */
    public function setBaseRefunded($baseRefunded);

    /**
    * Get base refunded
    *
    * @return float
    */
    public function getBaseRefunded();

    /**
    * Set base cost
    *
    * @param float $baseCost
    * @return $this
    */
    public function setBaseCost($baseCost);

    /**
    * Get base cost
    *
    * @return float
    */
    public function getBaseCost();

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
    * Set discount percent
    *
    * @param float $discountPercent
    * @return $this
    */
    public function setDiscountPercent($discountPercent);

    /**
    * Get discount percent
    *
    * @return float
    */
    public function getDiscountPercent();

    /**
    * Set discount invoiced
    *
    * @param float $discountInvoiced
    * @return $this
    */
    public function setDiscountInvoiced($discountInvoiced);

    /**
    * Get discount invoiced
    *
    * @return float
    */
    public function getDiscountInvoiced();

    /**
    * Set base discount invoiced
    *
    * @param float $baseDiscountInvoiced
    * @return $this
    */
    public function setBaseDiscountInvoiced($baseDiscountInvoiced);

    /**
    * Get base discount invoiced
    *
    * @return float
    */
    public function getBaseDiscountInvoiced();

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
    * Set discount refunded
    *
    * @param float $discountRefunded
    * @return $this
    */
    public function setDiscountRefunded($discountRefunded);

    /**
    * Get discount refunded
    *
    * @return float
    */
    public function getDiscountRefunded();

    /**
    * Set base discount refunded
    *
    * @param float $baseDiscountRefunded
    * @return $this
    */
    public function setBaseDiscountRefunded($baseDiscountRefunded);

    /**
    * Get base discount refunded
    *
    * @return float
    */
    public function getBaseDiscountRefunded();

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
    * Set original price
    *
    * @param float $originalPrice
    * @return $this
    */
    public function setOriginalPrice($originalPrice);

    /**
    * Get original price
    *
    * @return float
    */
    public function getOriginalPrice();

    /**
    * Set base original price
    *
    * @param float $baseOriginalPrice
    * @return $this
    */
    public function setBaseOriginalPrice($baseOriginalPrice);

    /**
    * Get base original price
    *
    * @return float
    */
    public function getBaseOriginalPrice();

    /**
    * Set total
    *
    * @param float $total
    * @return $this
    */
    public function setTotal($total);

    /**
    * Get total
    *
    * @return float
    */
    public function getTotal();

    /**
    * Set base total
    *
    * @param float $baseTotal
    * @return $this
    */
    public function setBaseTotal($baseTotal);

    /**
    * Get base total
    *
    * @return float
    */
    public function getBaseTotal();

    /**
    * Set total incl tax
    *
    * @param float $totalInclTax
    * @return $this
    */
    public function setTotalInclTax($totalInclTax);

    /**
    * Get total incl tax
    *
    * @return float
    */
    public function getTotalInclTax();

    /**
    * Set base total incl tax
    *
    * @param float $baseTotalInclTax
    * @return $this
    */
    public function setBaseTotalInclTax($baseTotalInclTax);

    /**
    * Get base total incl tax
    *
    * @return float
    */
    public function getBaseTotalInclTax();

    /**
    * Set qty invoiced
    *
    * @param float $qtyInvoiced
    * @return $this
    */
    public function setQtyInvoiced($qtyInvoiced);

    /**
    * Get qty invoiced
    *
    * @return float
    */
    public function getQtyInvoiced();

    /**
    * Set qty back ordered
    *
    * @param float $qtyBackOrdered
    * @return $this
    */
    public function setQtyBackOrdered($qtyBackOrdered);

    /**
    * Get qty back ordered
    *
    * @return float
    */
    public function getQtyBackOrdered();

    /**
    * Set qty cancelled
    *
    * @param float $qtyCancelled
    * @return $this
    */
    public function setQtyCancelled($qtyCancelled);

    /**
    * Get qty cancelled
    *
    * @return float
    */
    public function getQtyCancelled();

    /**
    * Set qty ordered
    *
    * @param float $qtyOrdered
    * @return $this
    */
    public function setQtyOrdered($qtyOrdered);

    /**
    * Get qty ordered
    *
    * @return float
    */
    public function getQtyOrdered();

    /**
    * Set qty refunded
    *
    * @param float $qtyRefunded
    * @return $this
    */
    public function setQtyRefunded($qtyRefunded);

    /**
    * Get qty refunded
    *
    * @return float
    */
    public function getQtyRefunded();

    /**
    * Set qty returned
    *
    * @param float $qtyReturned
    * @return $this
    */
    public function setQtyReturned($qtyReturned);

    /**
    * Get qty returned
    *
    * @return float
    */
    public function getQtyReturned();

    /**
    * Set qty shipped
    *
    * @param float $qtyShipped
    * @return $this
    */
    public function setQtyShipped($qtyShipped);

    /**
    * Get qty shipped
    *
    * @return float
    */
    public function getQtyShipped();

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
    * Set is free shipping
    *
    * @param bool $isFreeShipping
    * @return $this
    */
    public function setIsFreeShipping($isFreeShipping);

    /**
    * Get is free shipping
    *
    * @return bool
    */
    public function getIsFreeShipping();

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
    * Set additional data
    *
    * @param string $additionalData
    * @return $this
    */
    public function setAdditionalData($additionalData);

    /**
    * Get additional data
    *
    * @return string
    */
    public function getAdditionalData();

    /**
    * Set store id
    *
    * @param int $storeId
    * @return $this
    */
    public function setStoreId($storeId);

    /**
    * Get store id
    *
    * @return int
    */
    public function getStoreId();
}
