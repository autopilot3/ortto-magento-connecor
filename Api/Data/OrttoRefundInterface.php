<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoRefundInterface
{
    const ID = 'id';
    const INVOICE_ID = 'invoice_id';
    const NUMBER = 'number';
    const SUBTOTAL = 'subtotal';
    const BASE_SUBTOTAL = 'base_subtotal';
    const SUBTOTAL_INCL_TAX = 'subtotal_incl_tax';
    const BASE_SUBTOTAL_INCL_TAX = 'base_subtotal_incl_tax';
    const TAX = 'tax';
    const BASE_TAX = 'base_tax';
    const SHIPPING = 'shipping';
    const BASE_SHIPPING = 'base_shipping';
    const SHIPPING_INCL_TAX = 'shipping_incl_tax';
    const BASE_SHIPPING_INCL_TAX = 'base_shipping_incl_tax';
    const GRAND_TOTAL = 'grand_total';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const ADJUSTMENT = 'adjustment';
    const BASE_ADJUSTMENT = 'base_adjustment';
    const REFUNDED_AT = 'refunded_at';
    const ORDER_ITEM_IDS = 'order_item_ids';

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
    * Set invoice id
    *
    * @param int $invoiceId
    * @return $this
    */
    public function setInvoiceId($invoiceId);

    /**
    * Get invoice id
    *
    * @return int
    */
    public function getInvoiceId();

    /**
    * Set number
    *
    * @param string $number
    * @return $this
    */
    public function setNumber($number);

    /**
    * Get number
    *
    * @return string
    */
    public function getNumber();

    /**
    * Set subtotal
    *
    * @param float $subtotal
    * @return $this
    */
    public function setSubtotal($subtotal);

    /**
    * Get subtotal
    *
    * @return float
    */
    public function getSubtotal();

    /**
    * Set base subtotal
    *
    * @param float $baseSubtotal
    * @return $this
    */
    public function setBaseSubtotal($baseSubtotal);

    /**
    * Get base subtotal
    *
    * @return float
    */
    public function getBaseSubtotal();

    /**
    * Set subtotal incl tax
    *
    * @param float $subtotalInclTax
    * @return $this
    */
    public function setSubtotalInclTax($subtotalInclTax);

    /**
    * Get subtotal incl tax
    *
    * @return float
    */
    public function getSubtotalInclTax();

    /**
    * Set base subtotal incl tax
    *
    * @param float $baseSubtotalInclTax
    * @return $this
    */
    public function setBaseSubtotalInclTax($baseSubtotalInclTax);

    /**
    * Get base subtotal incl tax
    *
    * @return float
    */
    public function getBaseSubtotalInclTax();

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
    * Set shipping
    *
    * @param float $shipping
    * @return $this
    */
    public function setShipping($shipping);

    /**
    * Get shipping
    *
    * @return float
    */
    public function getShipping();

    /**
    * Set base shipping
    *
    * @param float $baseShipping
    * @return $this
    */
    public function setBaseShipping($baseShipping);

    /**
    * Get base shipping
    *
    * @return float
    */
    public function getBaseShipping();

    /**
    * Set shipping incl tax
    *
    * @param float $shippingInclTax
    * @return $this
    */
    public function setShippingInclTax($shippingInclTax);

    /**
    * Get shipping incl tax
    *
    * @return float
    */
    public function getShippingInclTax();

    /**
    * Set base shipping incl tax
    *
    * @param float $baseShippingInclTax
    * @return $this
    */
    public function setBaseShippingInclTax($baseShippingInclTax);

    /**
    * Get base shipping incl tax
    *
    * @return float
    */
    public function getBaseShippingInclTax();

    /**
    * Set grand total
    *
    * @param float $grandTotal
    * @return $this
    */
    public function setGrandTotal($grandTotal);

    /**
    * Get grand total
    *
    * @return float
    */
    public function getGrandTotal();

    /**
    * Set base grand total
    *
    * @param float $baseGrandTotal
    * @return $this
    */
    public function setBaseGrandTotal($baseGrandTotal);

    /**
    * Get base grand total
    *
    * @return float
    */
    public function getBaseGrandTotal();

    /**
    * Set adjustment
    *
    * @param float $adjustment
    * @return $this
    */
    public function setAdjustment($adjustment);

    /**
    * Get adjustment
    *
    * @return float
    */
    public function getAdjustment();

    /**
    * Set base adjustment
    *
    * @param float $baseAdjustment
    * @return $this
    */
    public function setBaseAdjustment($baseAdjustment);

    /**
    * Get base adjustment
    *
    * @return float
    */
    public function getBaseAdjustment();

    /**
    * Set refunded at
    *
    * @param string $refundedAt
    * @return $this
    */
    public function setRefundedAt($refundedAt);

    /**
    * Get refunded at
    *
    * @return string
    */
    public function getRefundedAt();

    /**
    * Set order item ids
    *
    * @param int[] $orderItemIds
    * @return $this
    */
    public function setOrderItemIds(array $orderItemIds);

    /**
    * Get order item ids
    *
    * @return int[]
    */
    public function getOrderItemIds(): array;

    /**
    * Convert object data to array
    *
    * @return array
    */
    public function serializeToArray();
}
