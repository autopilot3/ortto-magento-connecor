<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoCartInterface;
use Ortto\Connector\Helper\To;

class OrttoCart extends DataObject implements OrttoCartInterface
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
    public function setIpAddress($ipAddress)
    {
        return $this->setData(self::IP_ADDRESS, $ipAddress);
    }

    /** @inheirtDoc */
    public function getIpAddress()
    {
        return (string)$this->getData(self::IP_ADDRESS);
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
    public function setItemsCount($itemsCount)
    {
        return $this->setData(self::ITEMS_COUNT, $itemsCount);
    }

    /** @inheirtDoc */
    public function getItemsCount()
    {
        return To::int($this->getData(self::ITEMS_COUNT));
    }

    /** @inheirtDoc */
    public function setItemsQuantity($itemsQuantity)
    {
        return $this->setData(self::ITEMS_QUANTITY, $itemsQuantity);
    }

    /** @inheirtDoc */
    public function getItemsQuantity()
    {
        return To::int($this->getData(self::ITEMS_QUANTITY));
    }

    /** @inheirtDoc */
    public function setCurrencyCode($currencyCode)
    {
        return $this->setData(self::CURRENCY_CODE, $currencyCode);
    }

    /** @inheirtDoc */
    public function getCurrencyCode()
    {
        return (string)$this->getData(self::CURRENCY_CODE);
    }

    /** @inheirtDoc */
    public function setBaseCurrencyCode($baseCurrencyCode)
    {
        return $this->setData(self::BASE_CURRENCY_CODE, $baseCurrencyCode);
    }

    /** @inheirtDoc */
    public function getBaseCurrencyCode()
    {
        return (string)$this->getData(self::BASE_CURRENCY_CODE);
    }

    /** @inheirtDoc */
    public function setStoreCurrencyCode($storeCurrencyCode)
    {
        return $this->setData(self::STORE_CURRENCY_CODE, $storeCurrencyCode);
    }

    /** @inheirtDoc */
    public function getStoreCurrencyCode()
    {
        return (string)$this->getData(self::STORE_CURRENCY_CODE);
    }

    /** @inheirtDoc */
    public function setDiscountCodes(array $discountCodes)
    {
        return $this->setData(self::DISCOUNT_CODES, $discountCodes);
    }

    /** @inheirtDoc */
    public function getDiscountCodes(): array
    {
        return $this->getData(self::DISCOUNT_CODES) ?? [];
    }

    /** @inheirtDoc */
    public function setItems(array $items)
    {
        return $this->setData(self::ITEMS, $items);
    }

    /** @inheirtDoc */
    public function getItems(): array
    {
        return $this->getData(self::ITEMS) ?? [];
    }

    /** @inheirtDoc */
    public function setGrandTotal($grandTotal)
    {
        return $this->setData(self::GRAND_TOTAL, $grandTotal);
    }

    /** @inheirtDoc */
    public function getGrandTotal()
    {
        return To::float($this->getData(self::GRAND_TOTAL));
    }

    /** @inheirtDoc */
    public function setBaseGrandTotal($baseGrandTotal)
    {
        return $this->setData(self::BASE_GRAND_TOTAL, $baseGrandTotal);
    }

    /** @inheirtDoc */
    public function getBaseGrandTotal()
    {
        return To::float($this->getData(self::BASE_GRAND_TOTAL));
    }

    /** @inheirtDoc */
    public function setSubtotal($subtotal)
    {
        return $this->setData(self::SUBTOTAL, $subtotal);
    }

    /** @inheirtDoc */
    public function getSubtotal()
    {
        return To::float($this->getData(self::SUBTOTAL));
    }

    /** @inheirtDoc */
    public function setBaseSubtotal($baseSubtotal)
    {
        return $this->setData(self::BASE_SUBTOTAL, $baseSubtotal);
    }

    /** @inheirtDoc */
    public function getBaseSubtotal()
    {
        return To::float($this->getData(self::BASE_SUBTOTAL));
    }

    /** @inheirtDoc */
    public function setSubtotalWithDiscount($subtotalWithDiscount)
    {
        return $this->setData(self::SUBTOTAL_WITH_DISCOUNT, $subtotalWithDiscount);
    }

    /** @inheirtDoc */
    public function getSubtotalWithDiscount()
    {
        return To::float($this->getData(self::SUBTOTAL_WITH_DISCOUNT));
    }

    /** @inheirtDoc */
    public function setBaseSubtotalWithDiscount($baseSubtotalWithDiscount)
    {
        return $this->setData(self::BASE_SUBTOTAL_WITH_DISCOUNT, $baseSubtotalWithDiscount);
    }

    /** @inheirtDoc */
    public function getBaseSubtotalWithDiscount()
    {
        return To::float($this->getData(self::BASE_SUBTOTAL_WITH_DISCOUNT));
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
    public function setShipping($shipping)
    {
        return $this->setData(self::SHIPPING, $shipping);
    }

    /** @inheirtDoc */
    public function getShipping()
    {
        return To::float($this->getData(self::SHIPPING));
    }

    /** @inheirtDoc */
    public function setBaseShipping($baseShipping)
    {
        return $this->setData(self::BASE_SHIPPING, $baseShipping);
    }

    /** @inheirtDoc */
    public function getBaseShipping()
    {
        return To::float($this->getData(self::BASE_SHIPPING));
    }

    /** @inheirtDoc */
    public function setShippingTax($shippingTax)
    {
        return $this->setData(self::SHIPPING_TAX, $shippingTax);
    }

    /** @inheirtDoc */
    public function getShippingTax()
    {
        return To::float($this->getData(self::SHIPPING_TAX));
    }

    /** @inheirtDoc */
    public function setBaseShippingTax($baseShippingTax)
    {
        return $this->setData(self::BASE_SHIPPING_TAX, $baseShippingTax);
    }

    /** @inheirtDoc */
    public function getBaseShippingTax()
    {
        return To::float($this->getData(self::BASE_SHIPPING_TAX));
    }

    /** @inheirtDoc */
    public function setShippingAddress($shippingAddress)
    {
        return $this->setData(self::SHIPPING_ADDRESS, $shippingAddress);
    }

    /** @inheirtDoc */
    public function getShippingAddress()
    {
        return $this->getData(self::SHIPPING_ADDRESS);
    }

    /** @inheirtDoc */
    public function setBillingAddress($billingAddress)
    {
        return $this->setData(self::BILLING_ADDRESS, $billingAddress);
    }

    /** @inheirtDoc */
    public function getBillingAddress()
    {
        return $this->getData(self::BILLING_ADDRESS);
    }

    /** @inheirtDoc */
    public function setCartUrl($cartUrl)
    {
        return $this->setData(self::CART_URL, $cartUrl);
    }

    /** @inheirtDoc */
    public function getCartUrl()
    {
        return (string)$this->getData(self::CART_URL);
    }

    /** @inheirtDoc */
    public function setCheckoutUrl($checkoutUrl)
    {
        return $this->setData(self::CHECKOUT_URL, $checkoutUrl);
    }

    /** @inheirtDoc */
    public function getCheckoutUrl()
    {
        return (string)$this->getData(self::CHECKOUT_URL);
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
    public function serializeToArray()
    {
        if ($this == null) {
            return null;
        }
        $result=[];
        $result[self::ID] = $this->getId();
        $result[self::IP_ADDRESS] = $this->getIpAddress();
        $result[self::CREATED_AT] = $this->getCreatedAt();
        $result[self::UPDATED_AT] = $this->getUpdatedAt();
        $result[self::ITEMS_COUNT] = $this->getItemsCount();
        $result[self::ITEMS_QUANTITY] = $this->getItemsQuantity();
        $result[self::CURRENCY_CODE] = $this->getCurrencyCode();
        $result[self::BASE_CURRENCY_CODE] = $this->getBaseCurrencyCode();
        $result[self::STORE_CURRENCY_CODE] = $this->getStoreCurrencyCode();
        $result[self::DISCOUNT_CODES] = $this->getDiscountCodes();
        $result[self::ITEMS] = [];
        foreach ($this->getItems() as $item) {
            $result[self::ITEMS][] = $item->serializeToArray();
        }
        $result[self::GRAND_TOTAL] = $this->getGrandTotal();
        $result[self::BASE_GRAND_TOTAL] = $this->getBaseGrandTotal();
        $result[self::SUBTOTAL] = $this->getSubtotal();
        $result[self::BASE_SUBTOTAL] = $this->getBaseSubtotal();
        $result[self::SUBTOTAL_WITH_DISCOUNT] = $this->getSubtotalWithDiscount();
        $result[self::BASE_SUBTOTAL_WITH_DISCOUNT] = $this->getBaseSubtotalWithDiscount();
        $result[self::TAX] = $this->getTax();
        $result[self::BASE_TAX] = $this->getBaseTax();
        $result[self::SHIPPING] = $this->getShipping();
        $result[self::BASE_SHIPPING] = $this->getBaseShipping();
        $result[self::SHIPPING_TAX] = $this->getShippingTax();
        $result[self::BASE_SHIPPING_TAX] = $this->getBaseShippingTax();
        $shippingAddress = $this->getShippingAddress();
        $result[self::SHIPPING_ADDRESS] = $shippingAddress != null ? $shippingAddress->serializeToArray() : null;
        $billingAddress = $this->getBillingAddress();
        $result[self::BILLING_ADDRESS] = $billingAddress != null ? $billingAddress->serializeToArray() : null;
        $result[self::CART_URL] = $this->getCartUrl();
        $result[self::CHECKOUT_URL] = $this->getCheckoutUrl();
        $result[self::DISCOUNT] = $this->getDiscount();
        $result[self::BASE_DISCOUNT] = $this->getBaseDiscount();
        return $result;
    }
}
