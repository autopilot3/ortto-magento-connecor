<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoRefundItemInterface
{
    const ID = 'id';
    const PRODUCT = 'product';
    const TOTAL_REFUNDED = 'total_refunded';
    const BASE_TOTAL_REFUNDED = 'base_total_refunded';
    const DISCOUNT_REFUNDED = 'discount_refunded';
    const BASE_DISCOUNT_REFUNDED = 'base_discount_refunded';
    const REFUND_QUANTITY = 'refund_quantity';
    const ORDER_ITEM_ID = 'order_item_id';

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
    * Set total refunded
    *
    * @param float $totalRefunded
    * @return $this
    */
    public function setTotalRefunded($totalRefunded);

    /**
    * Get total refunded
    *
    * @return float
    */
    public function getTotalRefunded();

    /**
    * Set base total refunded
    *
    * @param float $baseTotalRefunded
    * @return $this
    */
    public function setBaseTotalRefunded($baseTotalRefunded);

    /**
    * Get base total refunded
    *
    * @return float
    */
    public function getBaseTotalRefunded();

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
    * Set refund quantity
    *
    * @param float $refundQuantity
    * @return $this
    */
    public function setRefundQuantity($refundQuantity);

    /**
    * Get refund quantity
    *
    * @return float
    */
    public function getRefundQuantity();

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
    * Convert object data to array
    *
    * @return array
    */
    public function serializeToArray();
}
