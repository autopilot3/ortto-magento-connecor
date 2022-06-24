<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoRefundItemInterface
{
    const ID = 'id';
    const ORDER_ITEM_ID = 'order_item_id';
    const PRODUCT_ID = 'product_id';
    const SKU = 'sku';
    const NAME = 'name';
    const PRICE = 'price';
    const PRICE_INCL_TAX = 'price_incl_tax';
    const BASE_PRICE = 'base_price';
    const BASE_PRICE_INCL_TAX = 'base_price_incl_tax';
    const QUANTITY = 'quantity';
    const TAX = 'tax';
    const BASE_TAX = 'base_tax';
    const TOTAL = 'total';
    const BASE_TOTAL = 'base_total';
    const TOTAL_INCL_TAX = 'total_incl_tax';
    const BASE_TOTAL_INCL_TAX = 'base_total_incl_tax';
    const DESCRIPTION = 'description';
    const DISCOUNT = 'discount';
    const BASE_DISCOUNT = 'base_discount';

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
    * Set order item id
    *
    * @param int $orderItemId
    * @return $this
    */
    public function setOrderItemId($orderItemId);

    /**
    * Get order item id
    *
    * @return int
    */
    public function getOrderItemId();

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
}
