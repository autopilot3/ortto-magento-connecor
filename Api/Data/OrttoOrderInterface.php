<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoOrderInterface
{
    const ID = 'id';
    const NUMBER = 'number';
    const CART_ID = 'cart_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const CANCELED_AT = 'canceled_at';
    const COMPLETED_AT = 'completed_at';
    const STATUS = 'status';
    const STATE = 'state';
    const BASE_CURRENCY_CODE = 'base_currency_code';
    const GLOBAL_CURRENCY_CODE = 'global_currency_code';
    const ORDER_CURRENCY_CODE = 'order_currency_code';
    const QUANTITY = 'quantity';
    const BASE_QUANTITY = 'base_quantity';
    const GRAND_TOTAL = 'grand_total';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const TOTAL_DUE = 'total_due';
    const BASE_TOTAL_DUE = 'base_total_due';
    const TOTAL_CANCELLED = 'total_cancelled';
    const BASE_TOTAL_CANCELLED = 'base_total_cancelled';
    const TOTAL_INVOICED = 'total_invoiced';
    const BASE_TOTAL_INVOICED = 'base_total_invoiced';
    const SUBTOTAL = 'subtotal';
    const BASE_SUBTOTAL = 'base_subtotal';
    const SUBTOTAL_INCL_TAX = 'subtotal_incl_tax';
    const BASE_SUBTOTAL_INCL_TAX = 'base_subtotal_incl_tax';
    const TOTAL_REFUNDED = 'total_refunded';
    const BASE_TOTAL_REFUNDED = 'base_total_refunded';
    const SUBTOTAL_REFUNDED = 'subtotal_refunded';
    const BASE_SUBTOTAL_REFUNDED = 'base_subtotal_refunded';
    const TOTAL_PAID = 'total_paid';
    const BASE_TOTAL_PAID = 'base_total_paid';
    const IP_ADDRESS = 'ip_address';
    const TAX = 'tax';
    const BASE_TAX = 'base_tax';
    const TAX_CANCELLED = 'tax_cancelled';
    const BASE_TAX_CANCELLED = 'base_tax_cancelled';
    const TAX_INVOICED = 'tax_invoiced';
    const BASE_TAX_INVOICED = 'base_tax_invoiced';
    const TAX_REFUNDED = 'tax_refunded';
    const BASE_TAX_REFUNDED = 'base_tax_refunded';
    const SHIPPING = 'shipping';
    const BASE_SHIPPING = 'base_shipping';
    const SHIPPING_INCL_TAX = 'shipping_incl_tax';
    const BASE_SHIPPING_INCL_TAX = 'base_shipping_incl_tax';
    const SHIPPING_TAX = 'shipping_tax';
    const BASE_SHIPPING_TAX = 'base_shipping_tax';
    const SHIPPING_CANCELLED = 'shipping_cancelled';
    const BASE_SHIPPING_CANCELLED = 'base_shipping_cancelled';
    const SHIPPING_INVOICED = 'shipping_invoiced';
    const BASE_SHIPPING_INVOICED = 'base_shipping_invoiced';
    const SHIPPING_REFUNDED = 'shipping_refunded';
    const BASE_SHIPPING_REFUNDED = 'base_shipping_refunded';
    const DISCOUNT = 'discount';
    const BASE_DISCOUNT = 'base_discount';
    const DISCOUNT_DESCRIPTION = 'discount_description';
    const DISCOUNT_REFUNDED = 'discount_refunded';
    const BASE_DISCOUNT_REFUNDED = 'base_discount_refunded';
    const DISCOUNT_INVOICED = 'discount_invoiced';
    const BASE_DISCOUNT_INVOICED = 'base_discount_invoiced';
    const DISCOUNT_CANCELLED = 'discount_cancelled';
    const BASE_DISCOUNT_CANCELLED = 'base_discount_cancelled';
    const SHIPPING_DISCOUNT = 'shipping_discount';
    const BASE_SHIPPING_DISCOUNT = 'base_shipping_discount';
    const LAST_TRANSACTION_ID = 'last_transaction_id';
    const PAYMENT_METHOD = 'payment_method';
    const DISCOUNT_CODES = 'discount_codes';
    const PROTECT_CODE = 'protect_code';
    const SHIPPING_ADDRESS = 'shipping_address';
    const BILLING_ADDRESS = 'billing_address';
    const ITEMS = 'items';
    const EXTENSION = 'extension';
    const REFUNDS = 'refunds';
    const CARRIERS = 'carriers';
    const CUSTOMER = 'customer';

    public const CUSTOMER_EMAIL = 'customer_email';
    public const CUSTOMER_ID = 'customer_id';
    public const ANONYMOUS_CUSTOMERS = 'anonymous';

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
     * Set cart id
     *
     * @param int $cartId
     * @return $this
     */
    public function setCartId($cartId);

    /**
     * Get cart id
     *
     * @return int
     */
    public function getCartId();

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
     * Set canceled at
     *
     * @param string $canceledAt
     * @return $this
     */
    public function setCanceledAt($canceledAt);

    /**
     * Get canceled at
     *
     * @return string
     */
    public function getCanceledAt();

    /**
     * Set completed at
     *
     * @param string $completedAt
     * @return $this
     */
    public function setCompletedAt($completedAt);

    /**
     * Get completed at
     *
     * @return string
     */
    public function getCompletedAt();

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set state
     *
     * @param string $state
     * @return $this
     */
    public function setState($state);

    /**
     * Get state
     *
     * @return string
     */
    public function getState();

    /**
     * Set base currency code
     *
     * @param string $baseCurrencyCode
     * @return $this
     */
    public function setBaseCurrencyCode($baseCurrencyCode);

    /**
     * Get base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode();

    /**
     * Set global currency code
     *
     * @param string $globalCurrencyCode
     * @return $this
     */
    public function setGlobalCurrencyCode($globalCurrencyCode);

    /**
     * Get global currency code
     *
     * @return string
     */
    public function getGlobalCurrencyCode();

    /**
     * Set order currency code
     *
     * @param string $orderCurrencyCode
     * @return $this
     */
    public function setOrderCurrencyCode($orderCurrencyCode);

    /**
     * Get order currency code
     *
     * @return string
     */
    public function getOrderCurrencyCode();

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
     * Set base quantity
     *
     * @param float $baseQuantity
     * @return $this
     */
    public function setBaseQuantity($baseQuantity);

    /**
     * Get base quantity
     *
     * @return float
     */
    public function getBaseQuantity();

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
     * Set total due
     *
     * @param float $totalDue
     * @return $this
     */
    public function setTotalDue($totalDue);

    /**
     * Get total due
     *
     * @return float
     */
    public function getTotalDue();

    /**
     * Set base total due
     *
     * @param float $baseTotalDue
     * @return $this
     */
    public function setBaseTotalDue($baseTotalDue);

    /**
     * Get base total due
     *
     * @return float
     */
    public function getBaseTotalDue();

    /**
     * Set total cancelled
     *
     * @param float $totalCancelled
     * @return $this
     */
    public function setTotalCancelled($totalCancelled);

    /**
     * Get total cancelled
     *
     * @return float
     */
    public function getTotalCancelled();

    /**
     * Set base total cancelled
     *
     * @param float $baseTotalCancelled
     * @return $this
     */
    public function setBaseTotalCancelled($baseTotalCancelled);

    /**
     * Get base total cancelled
     *
     * @return float
     */
    public function getBaseTotalCancelled();

    /**
     * Set total invoiced
     *
     * @param float $totalInvoiced
     * @return $this
     */
    public function setTotalInvoiced($totalInvoiced);

    /**
     * Get total invoiced
     *
     * @return float
     */
    public function getTotalInvoiced();

    /**
     * Set base total invoiced
     *
     * @param float $baseTotalInvoiced
     * @return $this
     */
    public function setBaseTotalInvoiced($baseTotalInvoiced);

    /**
     * Get base total invoiced
     *
     * @return float
     */
    public function getBaseTotalInvoiced();

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
     * Set subtotal refunded
     *
     * @param float $subtotalRefunded
     * @return $this
     */
    public function setSubtotalRefunded($subtotalRefunded);

    /**
     * Get subtotal refunded
     *
     * @return float
     */
    public function getSubtotalRefunded();

    /**
     * Set base subtotal refunded
     *
     * @param float $baseSubtotalRefunded
     * @return $this
     */
    public function setBaseSubtotalRefunded($baseSubtotalRefunded);

    /**
     * Get base subtotal refunded
     *
     * @return float
     */
    public function getBaseSubtotalRefunded();

    /**
     * Set total paid
     *
     * @param float $totalPaid
     * @return $this
     */
    public function setTotalPaid($totalPaid);

    /**
     * Get total paid
     *
     * @return float
     */
    public function getTotalPaid();

    /**
     * Set base total paid
     *
     * @param float $baseTotalPaid
     * @return $this
     */
    public function setBaseTotalPaid($baseTotalPaid);

    /**
     * Get base total paid
     *
     * @return float
     */
    public function getBaseTotalPaid();

    /**
     * Set ip address
     *
     * @param string $ipAddress
     * @return $this
     */
    public function setIpAddress($ipAddress);

    /**
     * Get ip address
     *
     * @return string
     */
    public function getIpAddress();

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
     * Set tax cancelled
     *
     * @param float $taxCancelled
     * @return $this
     */
    public function setTaxCancelled($taxCancelled);

    /**
     * Get tax cancelled
     *
     * @return float
     */
    public function getTaxCancelled();

    /**
     * Set base tax cancelled
     *
     * @param float $baseTaxCancelled
     * @return $this
     */
    public function setBaseTaxCancelled($baseTaxCancelled);

    /**
     * Get base tax cancelled
     *
     * @return float
     */
    public function getBaseTaxCancelled();

    /**
     * Set tax invoiced
     *
     * @param float $taxInvoiced
     * @return $this
     */
    public function setTaxInvoiced($taxInvoiced);

    /**
     * Get tax invoiced
     *
     * @return float
     */
    public function getTaxInvoiced();

    /**
     * Set base tax invoiced
     *
     * @param float $baseTaxInvoiced
     * @return $this
     */
    public function setBaseTaxInvoiced($baseTaxInvoiced);

    /**
     * Get base tax invoiced
     *
     * @return float
     */
    public function getBaseTaxInvoiced();

    /**
     * Set tax refunded
     *
     * @param float $taxRefunded
     * @return $this
     */
    public function setTaxRefunded($taxRefunded);

    /**
     * Get tax refunded
     *
     * @return float
     */
    public function getTaxRefunded();

    /**
     * Set base tax refunded
     *
     * @param float $baseTaxRefunded
     * @return $this
     */
    public function setBaseTaxRefunded($baseTaxRefunded);

    /**
     * Get base tax refunded
     *
     * @return float
     */
    public function getBaseTaxRefunded();

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
     * Set shipping tax
     *
     * @param float $shippingTax
     * @return $this
     */
    public function setShippingTax($shippingTax);

    /**
     * Get shipping tax
     *
     * @return float
     */
    public function getShippingTax();

    /**
     * Set base shipping tax
     *
     * @param float $baseShippingTax
     * @return $this
     */
    public function setBaseShippingTax($baseShippingTax);

    /**
     * Get base shipping tax
     *
     * @return float
     */
    public function getBaseShippingTax();

    /**
     * Set shipping cancelled
     *
     * @param float $shippingCancelled
     * @return $this
     */
    public function setShippingCancelled($shippingCancelled);

    /**
     * Get shipping cancelled
     *
     * @return float
     */
    public function getShippingCancelled();

    /**
     * Set base shipping cancelled
     *
     * @param float $baseShippingCancelled
     * @return $this
     */
    public function setBaseShippingCancelled($baseShippingCancelled);

    /**
     * Get base shipping cancelled
     *
     * @return float
     */
    public function getBaseShippingCancelled();

    /**
     * Set shipping invoiced
     *
     * @param float $shippingInvoiced
     * @return $this
     */
    public function setShippingInvoiced($shippingInvoiced);

    /**
     * Get shipping invoiced
     *
     * @return float
     */
    public function getShippingInvoiced();

    /**
     * Set base shipping invoiced
     *
     * @param float $baseShippingInvoiced
     * @return $this
     */
    public function setBaseShippingInvoiced($baseShippingInvoiced);

    /**
     * Get base shipping invoiced
     *
     * @return float
     */
    public function getBaseShippingInvoiced();

    /**
     * Set shipping refunded
     *
     * @param float $shippingRefunded
     * @return $this
     */
    public function setShippingRefunded($shippingRefunded);

    /**
     * Get shipping refunded
     *
     * @return float
     */
    public function getShippingRefunded();

    /**
     * Set base shipping refunded
     *
     * @param float $baseShippingRefunded
     * @return $this
     */
    public function setBaseShippingRefunded($baseShippingRefunded);

    /**
     * Get base shipping refunded
     *
     * @return float
     */
    public function getBaseShippingRefunded();

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
     * Set discount description
     *
     * @param string $discountDescription
     * @return $this
     */
    public function setDiscountDescription($discountDescription);

    /**
     * Get discount description
     *
     * @return string
     */
    public function getDiscountDescription();

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
     * Set discount cancelled
     *
     * @param float $discountCancelled
     * @return $this
     */
    public function setDiscountCancelled($discountCancelled);

    /**
     * Get discount cancelled
     *
     * @return float
     */
    public function getDiscountCancelled();

    /**
     * Set base discount cancelled
     *
     * @param float $baseDiscountCancelled
     * @return $this
     */
    public function setBaseDiscountCancelled($baseDiscountCancelled);

    /**
     * Get base discount cancelled
     *
     * @return float
     */
    public function getBaseDiscountCancelled();

    /**
     * Set shipping discount
     *
     * @param float $shippingDiscount
     * @return $this
     */
    public function setShippingDiscount($shippingDiscount);

    /**
     * Get shipping discount
     *
     * @return float
     */
    public function getShippingDiscount();

    /**
     * Set base shipping discount
     *
     * @param float $baseShippingDiscount
     * @return $this
     */
    public function setBaseShippingDiscount($baseShippingDiscount);

    /**
     * Get base shipping discount
     *
     * @return float
     */
    public function getBaseShippingDiscount();

    /**
     * Set last transaction id
     *
     * @param string $lastTransactionId
     * @return $this
     */
    public function setLastTransactionId($lastTransactionId);

    /**
     * Get last transaction id
     *
     * @return string
     */
    public function getLastTransactionId();

    /**
     * Set payment method
     *
     * @param string $paymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * Get payment method
     *
     * @return string
     */
    public function getPaymentMethod();

    /**
     * Set discount codes
     *
     * @param string[] $discountCodes
     * @return $this
     */
    public function setDiscountCodes(array $discountCodes);

    /**
     * Get discount codes
     *
     * @return string[]
     */
    public function getDiscountCodes(): array;

    /**
     * Set protect code
     *
     * @param string $protectCode
     * @return $this
     */
    public function setProtectCode($protectCode);

    /**
     * Get protect code
     *
     * @return string
     */
    public function getProtectCode();

    /**
     * Set shipping address
     *
     * @param \Ortto\Connector\Api\Data\OrttoAddressInterface|null $shippingAddress
     * @return $this
     */
    public function setShippingAddress($shippingAddress);

    /**
     * Get shipping address
     *
     * @return \Ortto\Connector\Api\Data\OrttoAddressInterface|null
     */
    public function getShippingAddress();

    /**
     * Set billing address
     *
     * @param \Ortto\Connector\Api\Data\OrttoAddressInterface|null $billingAddress
     * @return $this
     */
    public function setBillingAddress($billingAddress);

    /**
     * Get billing address
     *
     * @return \Ortto\Connector\Api\Data\OrttoAddressInterface|null
     */
    public function getBillingAddress();

    /**
     * Set items
     *
     * @param \Ortto\Connector\Api\Data\OrttoOrderItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get items
     *
     * @return \Ortto\Connector\Api\Data\OrttoOrderItemInterface[]
     */
    public function getItems(): array;

    /**
     * Set extension
     *
     * @param \Ortto\Connector\Api\Data\OrttoOrderExtensionInterface $extension
     * @return $this
     */
    public function setExtension($extension);

    /**
     * Get extension
     *
     * @return \Ortto\Connector\Api\Data\OrttoOrderExtensionInterface
     */
    public function getExtension();

    /**
     * Set refunds
     *
     * @param \Ortto\Connector\Api\Data\OrttoRefundInterface[] $refunds
     * @return $this
     */
    public function setRefunds(array $refunds);

    /**
     * Get refunds
     *
     * @return \Ortto\Connector\Api\Data\OrttoRefundInterface[]
     */
    public function getRefunds(): array;

    /**
     * Set carriers
     *
     * @param \Ortto\Connector\Api\Data\OrttoCarrierInterface[] $carriers
     * @return $this
     */
    public function setCarriers(array $carriers);

    /**
     * Get carriers
     *
     * @return \Ortto\Connector\Api\Data\OrttoCarrierInterface[]
     */
    public function getCarriers(): array;

    /**
     * Set customer
     *
     * @param \Ortto\Connector\Api\Data\OrttoCustomerInterface $customer
     * @return $this
     */
    public function setCustomer($customer);

    /**
     * Get customer
     *
     * @return \Ortto\Connector\Api\Data\OrttoCustomerInterface
     */
    public function getCustomer();

    /**
     * Convert object data to array
     *
     * @return array
     */
    public function serializeToArray();
}
