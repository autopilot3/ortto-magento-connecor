<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoCartInterface
{
    const ID = 'id';
    const IP_ADDRESS = 'ip_address';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const ITEMS_COUNT = 'items_count';
    const ITEMS_QUANTITY = 'items_quantity';
    const CURRENCY_CODE = 'currency_code';
    const BASE_CURRENCY_CODE = 'base_currency_code';
    const STORE_CURRENCY_CODE = 'store_currency_code';
    const DISCOUNT_CODES = 'discount_codes';
    const ITEMS = 'items';
    const GRAND_TOTAL = 'grand_total';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const SUBTOTAL = 'subtotal';
    const BASE_SUBTOTAL = 'base_subtotal';
    const SUBTOTAL_WITH_DISCOUNT = 'subtotal_with_discount';
    const BASE_SUBTOTAL_WITH_DISCOUNT = 'base_subtotal_with_discount';
    const TAX = 'tax';
    const BASE_TAX = 'base_tax';
    const SHIPPING = 'shipping';
    const BASE_SHIPPING = 'base_shipping';
    const SHIPPING_INCL_TAX = 'shipping_incl_tax';
    const BASE_SHIPPING_INCL_TAX = 'base_shipping_incl_tax';
    const SHIPPING_TAX = 'shipping_tax';
    const BASE_SHIPPING_TAX = 'base_shipping_tax';
    const SHIPPING_ADDRESS = 'shipping_address';
    const BILLING_ADDRESS = 'billing_address';
    const CART_URL = 'cart_url';
    const CHECKOUT_URL = 'checkout_url';
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
    * Set items count
    *
    * @param int $itemsCount
    * @return $this
    */
    public function setItemsCount($itemsCount);

    /**
    * Get items count
    *
    * @return int
    */
    public function getItemsCount();

    /**
    * Set items quantity
    *
    * @param int $itemsQuantity
    * @return $this
    */
    public function setItemsQuantity($itemsQuantity);

    /**
    * Get items quantity
    *
    * @return int
    */
    public function getItemsQuantity();

    /**
    * Set currency code
    *
    * @param string $currencyCode
    * @return $this
    */
    public function setCurrencyCode($currencyCode);

    /**
    * Get currency code
    *
    * @return string
    */
    public function getCurrencyCode();

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
    * Set store currency code
    *
    * @param string $storeCurrencyCode
    * @return $this
    */
    public function setStoreCurrencyCode($storeCurrencyCode);

    /**
    * Get store currency code
    *
    * @return string
    */
    public function getStoreCurrencyCode();

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
    * Set items
    *
    * @param \Ortto\Connector\Api\Data\OrttoCartItemInterface[] $items
    * @return $this
    */
    public function setItems(array $items);

    /**
    * Get items
    *
    * @return \Ortto\Connector\Api\Data\OrttoCartItemInterface[]
    */
    public function getItems(): array;

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
    * Set subtotal with discount
    *
    * @param float $subtotalWithDiscount
    * @return $this
    */
    public function setSubtotalWithDiscount($subtotalWithDiscount);

    /**
    * Get subtotal with discount
    *
    * @return float
    */
    public function getSubtotalWithDiscount();

    /**
    * Set base subtotal with discount
    *
    * @param float $baseSubtotalWithDiscount
    * @return $this
    */
    public function setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);

    /**
    * Get base subtotal with discount
    *
    * @return float
    */
    public function getBaseSubtotalWithDiscount();

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
    * Set cart url
    *
    * @param string $cartUrl
    * @return $this
    */
    public function setCartUrl($cartUrl);

    /**
    * Get cart url
    *
    * @return string
    */
    public function getCartUrl();

    /**
    * Set checkout url
    *
    * @param string $checkoutUrl
    * @return $this
    */
    public function setCheckoutUrl($checkoutUrl);

    /**
    * Get checkout url
    *
    * @return string
    */
    public function getCheckoutUrl();

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
