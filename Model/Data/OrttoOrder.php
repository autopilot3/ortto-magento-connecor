<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoOrderInterface;
use Ortto\Connector\Helper\To;

class OrttoOrder extends DataObject implements OrttoOrderInterface
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
    public function setNumber($number)
    {
        return $this->setData(self::NUMBER, $number);
    }

    /** @inheirtDoc */
    public function getNumber()
    {
        return (string)$this->getData(self::NUMBER);
    }

    /** @inheirtDoc */
    public function setCartId($cartId)
    {
        return $this->setData(self::CART_ID, $cartId);
    }

    /** @inheirtDoc */
    public function getCartId()
    {
        return To::int($this->getData(self::CART_ID));
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
    public function setCanceledAt($canceledAt)
    {
        return $this->setData(self::CANCELED_AT, $canceledAt);
    }

    /** @inheirtDoc */
    public function getCanceledAt()
    {
        return (string)$this->getData(self::CANCELED_AT);
    }

    /** @inheirtDoc */
    public function setCompletedAt($completedAt)
    {
        return $this->setData(self::COMPLETED_AT, $completedAt);
    }

    /** @inheirtDoc */
    public function getCompletedAt()
    {
        return (string)$this->getData(self::COMPLETED_AT);
    }

    /** @inheirtDoc */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /** @inheirtDoc */
    public function getStatus()
    {
        return (string)$this->getData(self::STATUS);
    }

    /** @inheirtDoc */
    public function setState($state)
    {
        return $this->setData(self::STATE, $state);
    }

    /** @inheirtDoc */
    public function getState()
    {
        return (string)$this->getData(self::STATE);
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
    public function setGlobalCurrencyCode($globalCurrencyCode)
    {
        return $this->setData(self::GLOBAL_CURRENCY_CODE, $globalCurrencyCode);
    }

    /** @inheirtDoc */
    public function getGlobalCurrencyCode()
    {
        return (string)$this->getData(self::GLOBAL_CURRENCY_CODE);
    }

    /** @inheirtDoc */
    public function setOrderCurrencyCode($orderCurrencyCode)
    {
        return $this->setData(self::ORDER_CURRENCY_CODE, $orderCurrencyCode);
    }

    /** @inheirtDoc */
    public function getOrderCurrencyCode()
    {
        return (string)$this->getData(self::ORDER_CURRENCY_CODE);
    }

    /** @inheirtDoc */
    public function setQuantity($quantity)
    {
        return $this->setData(self::QUANTITY, $quantity);
    }

    /** @inheirtDoc */
    public function getQuantity()
    {
        return To::float($this->getData(self::QUANTITY));
    }

    /** @inheirtDoc */
    public function setBaseQuantity($baseQuantity)
    {
        return $this->setData(self::BASE_QUANTITY, $baseQuantity);
    }

    /** @inheirtDoc */
    public function getBaseQuantity()
    {
        return To::float($this->getData(self::BASE_QUANTITY));
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
    public function setTotalDue($totalDue)
    {
        return $this->setData(self::TOTAL_DUE, $totalDue);
    }

    /** @inheirtDoc */
    public function getTotalDue()
    {
        return To::float($this->getData(self::TOTAL_DUE));
    }

    /** @inheirtDoc */
    public function setBaseTotalDue($baseTotalDue)
    {
        return $this->setData(self::BASE_TOTAL_DUE, $baseTotalDue);
    }

    /** @inheirtDoc */
    public function getBaseTotalDue()
    {
        return To::float($this->getData(self::BASE_TOTAL_DUE));
    }

    /** @inheirtDoc */
    public function setTotalCancelled($totalCancelled)
    {
        return $this->setData(self::TOTAL_CANCELLED, $totalCancelled);
    }

    /** @inheirtDoc */
    public function getTotalCancelled()
    {
        return To::float($this->getData(self::TOTAL_CANCELLED));
    }

    /** @inheirtDoc */
    public function setBaseTotalCancelled($baseTotalCancelled)
    {
        return $this->setData(self::BASE_TOTAL_CANCELLED, $baseTotalCancelled);
    }

    /** @inheirtDoc */
    public function getBaseTotalCancelled()
    {
        return To::float($this->getData(self::BASE_TOTAL_CANCELLED));
    }

    /** @inheirtDoc */
    public function setTotalInvoiced($totalInvoiced)
    {
        return $this->setData(self::TOTAL_INVOICED, $totalInvoiced);
    }

    /** @inheirtDoc */
    public function getTotalInvoiced()
    {
        return To::float($this->getData(self::TOTAL_INVOICED));
    }

    /** @inheirtDoc */
    public function setBaseTotalInvoiced($baseTotalInvoiced)
    {
        return $this->setData(self::BASE_TOTAL_INVOICED, $baseTotalInvoiced);
    }

    /** @inheirtDoc */
    public function getBaseTotalInvoiced()
    {
        return To::float($this->getData(self::BASE_TOTAL_INVOICED));
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
    public function setSubtotalInclTax($subtotalInclTax)
    {
        return $this->setData(self::SUBTOTAL_INCL_TAX, $subtotalInclTax);
    }

    /** @inheirtDoc */
    public function getSubtotalInclTax()
    {
        return To::float($this->getData(self::SUBTOTAL_INCL_TAX));
    }

    /** @inheirtDoc */
    public function setBaseSubtotalInclTax($baseSubtotalInclTax)
    {
        return $this->setData(self::BASE_SUBTOTAL_INCL_TAX, $baseSubtotalInclTax);
    }

    /** @inheirtDoc */
    public function getBaseSubtotalInclTax()
    {
        return To::float($this->getData(self::BASE_SUBTOTAL_INCL_TAX));
    }

    /** @inheirtDoc */
    public function setBaseTotalOfflineRefunded($baseTotalOfflineRefunded)
    {
        return $this->setData(self::BASE_TOTAL_OFFLINE_REFUNDED, $baseTotalOfflineRefunded);
    }

    /** @inheirtDoc */
    public function getBaseTotalOfflineRefunded()
    {
        return To::float($this->getData(self::BASE_TOTAL_OFFLINE_REFUNDED));
    }

    /** @inheirtDoc */
    public function setBaseTotalOnlineRefunded($baseTotalOnlineRefunded)
    {
        return $this->setData(self::BASE_TOTAL_ONLINE_REFUNDED, $baseTotalOnlineRefunded);
    }

    /** @inheirtDoc */
    public function getBaseTotalOnlineRefunded()
    {
        return To::float($this->getData(self::BASE_TOTAL_ONLINE_REFUNDED));
    }

    /** @inheirtDoc */
    public function setTotalOfflineRefunded($totalOfflineRefunded)
    {
        return $this->setData(self::TOTAL_OFFLINE_REFUNDED, $totalOfflineRefunded);
    }

    /** @inheirtDoc */
    public function getTotalOfflineRefunded()
    {
        return To::float($this->getData(self::TOTAL_OFFLINE_REFUNDED));
    }

    /** @inheirtDoc */
    public function setTotalOnlineRefunded($totalOnlineRefunded)
    {
        return $this->setData(self::TOTAL_ONLINE_REFUNDED, $totalOnlineRefunded);
    }

    /** @inheirtDoc */
    public function getTotalOnlineRefunded()
    {
        return To::float($this->getData(self::TOTAL_ONLINE_REFUNDED));
    }

    /** @inheirtDoc */
    public function setTotalPaid($totalPaid)
    {
        return $this->setData(self::TOTAL_PAID, $totalPaid);
    }

    /** @inheirtDoc */
    public function getTotalPaid()
    {
        return To::float($this->getData(self::TOTAL_PAID));
    }

    /** @inheirtDoc */
    public function setBaseTotalPaid($baseTotalPaid)
    {
        return $this->setData(self::BASE_TOTAL_PAID, $baseTotalPaid);
    }

    /** @inheirtDoc */
    public function getBaseTotalPaid()
    {
        return To::float($this->getData(self::BASE_TOTAL_PAID));
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
    public function setTaxCancelled($taxCancelled)
    {
        return $this->setData(self::TAX_CANCELLED, $taxCancelled);
    }

    /** @inheirtDoc */
    public function getTaxCancelled()
    {
        return To::float($this->getData(self::TAX_CANCELLED));
    }

    /** @inheirtDoc */
    public function setBaseTaxCancelled($baseTaxCancelled)
    {
        return $this->setData(self::BASE_TAX_CANCELLED, $baseTaxCancelled);
    }

    /** @inheirtDoc */
    public function getBaseTaxCancelled()
    {
        return To::float($this->getData(self::BASE_TAX_CANCELLED));
    }

    /** @inheirtDoc */
    public function setTaxInvoiced($taxInvoiced)
    {
        return $this->setData(self::TAX_INVOICED, $taxInvoiced);
    }

    /** @inheirtDoc */
    public function getTaxInvoiced()
    {
        return To::float($this->getData(self::TAX_INVOICED));
    }

    /** @inheirtDoc */
    public function setBaseTaxInvoiced($baseTaxInvoiced)
    {
        return $this->setData(self::BASE_TAX_INVOICED, $baseTaxInvoiced);
    }

    /** @inheirtDoc */
    public function getBaseTaxInvoiced()
    {
        return To::float($this->getData(self::BASE_TAX_INVOICED));
    }

    /** @inheirtDoc */
    public function setTaxRefunded($taxRefunded)
    {
        return $this->setData(self::TAX_REFUNDED, $taxRefunded);
    }

    /** @inheirtDoc */
    public function getTaxRefunded()
    {
        return To::float($this->getData(self::TAX_REFUNDED));
    }

    /** @inheirtDoc */
    public function setBaseTaxRefunded($baseTaxRefunded)
    {
        return $this->setData(self::BASE_TAX_REFUNDED, $baseTaxRefunded);
    }

    /** @inheirtDoc */
    public function getBaseTaxRefunded()
    {
        return To::float($this->getData(self::BASE_TAX_REFUNDED));
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
    public function setShippingInclTax($shippingInclTax)
    {
        return $this->setData(self::SHIPPING_INCL_TAX, $shippingInclTax);
    }

    /** @inheirtDoc */
    public function getShippingInclTax()
    {
        return To::float($this->getData(self::SHIPPING_INCL_TAX));
    }

    /** @inheirtDoc */
    public function setBaseShippingInclTax($baseShippingInclTax)
    {
        return $this->setData(self::BASE_SHIPPING_INCL_TAX, $baseShippingInclTax);
    }

    /** @inheirtDoc */
    public function getBaseShippingInclTax()
    {
        return To::float($this->getData(self::BASE_SHIPPING_INCL_TAX));
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
    public function setShippingCancelled($shippingCancelled)
    {
        return $this->setData(self::SHIPPING_CANCELLED, $shippingCancelled);
    }

    /** @inheirtDoc */
    public function getShippingCancelled()
    {
        return To::float($this->getData(self::SHIPPING_CANCELLED));
    }

    /** @inheirtDoc */
    public function setBaseShippingCancelled($baseShippingCancelled)
    {
        return $this->setData(self::BASE_SHIPPING_CANCELLED, $baseShippingCancelled);
    }

    /** @inheirtDoc */
    public function getBaseShippingCancelled()
    {
        return To::float($this->getData(self::BASE_SHIPPING_CANCELLED));
    }

    /** @inheirtDoc */
    public function setShippingInvoiced($shippingInvoiced)
    {
        return $this->setData(self::SHIPPING_INVOICED, $shippingInvoiced);
    }

    /** @inheirtDoc */
    public function getShippingInvoiced()
    {
        return To::float($this->getData(self::SHIPPING_INVOICED));
    }

    /** @inheirtDoc */
    public function setBaseShippingInvoiced($baseShippingInvoiced)
    {
        return $this->setData(self::BASE_SHIPPING_INVOICED, $baseShippingInvoiced);
    }

    /** @inheirtDoc */
    public function getBaseShippingInvoiced()
    {
        return To::float($this->getData(self::BASE_SHIPPING_INVOICED));
    }

    /** @inheirtDoc */
    public function setShippingRefunded($shippingRefunded)
    {
        return $this->setData(self::SHIPPING_REFUNDED, $shippingRefunded);
    }

    /** @inheirtDoc */
    public function getShippingRefunded()
    {
        return To::float($this->getData(self::SHIPPING_REFUNDED));
    }

    /** @inheirtDoc */
    public function setBaseShippingRefunded($baseShippingRefunded)
    {
        return $this->setData(self::BASE_SHIPPING_REFUNDED, $baseShippingRefunded);
    }

    /** @inheirtDoc */
    public function getBaseShippingRefunded()
    {
        return To::float($this->getData(self::BASE_SHIPPING_REFUNDED));
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
    public function setDiscountDescription($discountDescription)
    {
        return $this->setData(self::DISCOUNT_DESCRIPTION, $discountDescription);
    }

    /** @inheirtDoc */
    public function getDiscountDescription()
    {
        return (string)$this->getData(self::DISCOUNT_DESCRIPTION);
    }

    /** @inheirtDoc */
    public function setDiscountRefunded($discountRefunded)
    {
        return $this->setData(self::DISCOUNT_REFUNDED, $discountRefunded);
    }

    /** @inheirtDoc */
    public function getDiscountRefunded()
    {
        return To::float($this->getData(self::DISCOUNT_REFUNDED));
    }

    /** @inheirtDoc */
    public function setBaseDiscountRefunded($baseDiscountRefunded)
    {
        return $this->setData(self::BASE_DISCOUNT_REFUNDED, $baseDiscountRefunded);
    }

    /** @inheirtDoc */
    public function getBaseDiscountRefunded()
    {
        return To::float($this->getData(self::BASE_DISCOUNT_REFUNDED));
    }

    /** @inheirtDoc */
    public function setDiscountInvoiced($discountInvoiced)
    {
        return $this->setData(self::DISCOUNT_INVOICED, $discountInvoiced);
    }

    /** @inheirtDoc */
    public function getDiscountInvoiced()
    {
        return To::float($this->getData(self::DISCOUNT_INVOICED));
    }

    /** @inheirtDoc */
    public function setBaseDiscountInvoiced($baseDiscountInvoiced)
    {
        return $this->setData(self::BASE_DISCOUNT_INVOICED, $baseDiscountInvoiced);
    }

    /** @inheirtDoc */
    public function getBaseDiscountInvoiced()
    {
        return To::float($this->getData(self::BASE_DISCOUNT_INVOICED));
    }

    /** @inheirtDoc */
    public function setDiscountCancelled($discountCancelled)
    {
        return $this->setData(self::DISCOUNT_CANCELLED, $discountCancelled);
    }

    /** @inheirtDoc */
    public function getDiscountCancelled()
    {
        return To::float($this->getData(self::DISCOUNT_CANCELLED));
    }

    /** @inheirtDoc */
    public function setBaseDiscountCancelled($baseDiscountCancelled)
    {
        return $this->setData(self::BASE_DISCOUNT_CANCELLED, $baseDiscountCancelled);
    }

    /** @inheirtDoc */
    public function getBaseDiscountCancelled()
    {
        return To::float($this->getData(self::BASE_DISCOUNT_CANCELLED));
    }

    /** @inheirtDoc */
    public function setShippingDiscount($shippingDiscount)
    {
        return $this->setData(self::SHIPPING_DISCOUNT, $shippingDiscount);
    }

    /** @inheirtDoc */
    public function getShippingDiscount()
    {
        return To::float($this->getData(self::SHIPPING_DISCOUNT));
    }

    /** @inheirtDoc */
    public function setBaseShippingDiscount($baseShippingDiscount)
    {
        return $this->setData(self::BASE_SHIPPING_DISCOUNT, $baseShippingDiscount);
    }

    /** @inheirtDoc */
    public function getBaseShippingDiscount()
    {
        return To::float($this->getData(self::BASE_SHIPPING_DISCOUNT));
    }

    /** @inheirtDoc */
    public function setLastTransactionId($lastTransactionId)
    {
        return $this->setData(self::LAST_TRANSACTION_ID, $lastTransactionId);
    }

    /** @inheirtDoc */
    public function getLastTransactionId()
    {
        return (string)$this->getData(self::LAST_TRANSACTION_ID);
    }

    /** @inheirtDoc */
    public function setPaymentMethod($paymentMethod)
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    /** @inheirtDoc */
    public function getPaymentMethod()
    {
        return (string)$this->getData(self::PAYMENT_METHOD);
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
    public function setProtectCode($protectCode)
    {
        return $this->setData(self::PROTECT_CODE, $protectCode);
    }

    /** @inheirtDoc */
    public function getProtectCode()
    {
        return (string)$this->getData(self::PROTECT_CODE);
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
    public function setExtension($extension)
    {
        return $this->setData(self::EXTENSION, $extension);
    }

    /** @inheirtDoc */
    public function getExtension()
    {
        return $this->getData(self::EXTENSION);
    }

    /** @inheirtDoc */
    public function setRefunds(array $refunds)
    {
        return $this->setData(self::REFUNDS, $refunds);
    }

    /** @inheirtDoc */
    public function getRefunds(): array
    {
        return $this->getData(self::REFUNDS) ?? [];
    }

    /** @inheirtDoc */
    public function setCarriers(array $carriers)
    {
        return $this->setData(self::CARRIERS, $carriers);
    }

    /** @inheirtDoc */
    public function getCarriers(): array
    {
        return $this->getData(self::CARRIERS) ?? [];
    }

    /** @inheirtDoc */
    public function setCustomer($customer)
    {
        return $this->setData(self::CUSTOMER, $customer);
    }

    /** @inheirtDoc */
    public function getCustomer()
    {
        return $this->getData(self::CUSTOMER);
    }

    /** @inheirtDoc */
    public function serializeToArray()
    {
        if ($this == null) {
            return null;
        }
        $result=[];
        $result[self::ID] = $this->getId();
        $result[self::NUMBER] = $this->getNumber();
        $result[self::CART_ID] = $this->getCartId();
        $result[self::CREATED_AT] = $this->getCreatedAt();
        $result[self::UPDATED_AT] = $this->getUpdatedAt();
        $result[self::CANCELED_AT] = $this->getCanceledAt();
        $result[self::COMPLETED_AT] = $this->getCompletedAt();
        $result[self::STATUS] = $this->getStatus();
        $result[self::STATE] = $this->getState();
        $result[self::BASE_CURRENCY_CODE] = $this->getBaseCurrencyCode();
        $result[self::GLOBAL_CURRENCY_CODE] = $this->getGlobalCurrencyCode();
        $result[self::ORDER_CURRENCY_CODE] = $this->getOrderCurrencyCode();
        $result[self::QUANTITY] = $this->getQuantity();
        $result[self::BASE_QUANTITY] = $this->getBaseQuantity();
        $result[self::GRAND_TOTAL] = $this->getGrandTotal();
        $result[self::BASE_GRAND_TOTAL] = $this->getBaseGrandTotal();
        $result[self::TOTAL_DUE] = $this->getTotalDue();
        $result[self::BASE_TOTAL_DUE] = $this->getBaseTotalDue();
        $result[self::TOTAL_CANCELLED] = $this->getTotalCancelled();
        $result[self::BASE_TOTAL_CANCELLED] = $this->getBaseTotalCancelled();
        $result[self::TOTAL_INVOICED] = $this->getTotalInvoiced();
        $result[self::BASE_TOTAL_INVOICED] = $this->getBaseTotalInvoiced();
        $result[self::SUBTOTAL] = $this->getSubtotal();
        $result[self::BASE_SUBTOTAL] = $this->getBaseSubtotal();
        $result[self::SUBTOTAL_INCL_TAX] = $this->getSubtotalInclTax();
        $result[self::BASE_SUBTOTAL_INCL_TAX] = $this->getBaseSubtotalInclTax();
        $result[self::BASE_TOTAL_OFFLINE_REFUNDED] = $this->getBaseTotalOfflineRefunded();
        $result[self::BASE_TOTAL_ONLINE_REFUNDED] = $this->getBaseTotalOnlineRefunded();
        $result[self::TOTAL_OFFLINE_REFUNDED] = $this->getTotalOfflineRefunded();
        $result[self::TOTAL_ONLINE_REFUNDED] = $this->getTotalOnlineRefunded();
        $result[self::TOTAL_PAID] = $this->getTotalPaid();
        $result[self::BASE_TOTAL_PAID] = $this->getBaseTotalPaid();
        $result[self::IP_ADDRESS] = $this->getIpAddress();
        $result[self::TAX] = $this->getTax();
        $result[self::BASE_TAX] = $this->getBaseTax();
        $result[self::TAX_CANCELLED] = $this->getTaxCancelled();
        $result[self::BASE_TAX_CANCELLED] = $this->getBaseTaxCancelled();
        $result[self::TAX_INVOICED] = $this->getTaxInvoiced();
        $result[self::BASE_TAX_INVOICED] = $this->getBaseTaxInvoiced();
        $result[self::TAX_REFUNDED] = $this->getTaxRefunded();
        $result[self::BASE_TAX_REFUNDED] = $this->getBaseTaxRefunded();
        $result[self::SHIPPING] = $this->getShipping();
        $result[self::BASE_SHIPPING] = $this->getBaseShipping();
        $result[self::SHIPPING_INCL_TAX] = $this->getShippingInclTax();
        $result[self::BASE_SHIPPING_INCL_TAX] = $this->getBaseShippingInclTax();
        $result[self::SHIPPING_TAX] = $this->getShippingTax();
        $result[self::BASE_SHIPPING_TAX] = $this->getBaseShippingTax();
        $result[self::SHIPPING_CANCELLED] = $this->getShippingCancelled();
        $result[self::BASE_SHIPPING_CANCELLED] = $this->getBaseShippingCancelled();
        $result[self::SHIPPING_INVOICED] = $this->getShippingInvoiced();
        $result[self::BASE_SHIPPING_INVOICED] = $this->getBaseShippingInvoiced();
        $result[self::SHIPPING_REFUNDED] = $this->getShippingRefunded();
        $result[self::BASE_SHIPPING_REFUNDED] = $this->getBaseShippingRefunded();
        $result[self::DISCOUNT] = $this->getDiscount();
        $result[self::BASE_DISCOUNT] = $this->getBaseDiscount();
        $result[self::DISCOUNT_DESCRIPTION] = $this->getDiscountDescription();
        $result[self::DISCOUNT_REFUNDED] = $this->getDiscountRefunded();
        $result[self::BASE_DISCOUNT_REFUNDED] = $this->getBaseDiscountRefunded();
        $result[self::DISCOUNT_INVOICED] = $this->getDiscountInvoiced();
        $result[self::BASE_DISCOUNT_INVOICED] = $this->getBaseDiscountInvoiced();
        $result[self::DISCOUNT_CANCELLED] = $this->getDiscountCancelled();
        $result[self::BASE_DISCOUNT_CANCELLED] = $this->getBaseDiscountCancelled();
        $result[self::SHIPPING_DISCOUNT] = $this->getShippingDiscount();
        $result[self::BASE_SHIPPING_DISCOUNT] = $this->getBaseShippingDiscount();
        $result[self::LAST_TRANSACTION_ID] = $this->getLastTransactionId();
        $result[self::PAYMENT_METHOD] = $this->getPaymentMethod();
        $result[self::DISCOUNT_CODES] = $this->getDiscountCodes();
        $result[self::PROTECT_CODE] = $this->getProtectCode();
        $shippingAddress = $this->getShippingAddress();
        $result[self::SHIPPING_ADDRESS] = $shippingAddress != null ? $shippingAddress->serializeToArray() : null;
        $billingAddress = $this->getBillingAddress();
        $result[self::BILLING_ADDRESS] = $billingAddress != null ? $billingAddress->serializeToArray() : null;
        $result[self::ITEMS] = [];
        foreach ($this->getItems() as $item) {
            $result[self::ITEMS][] = $item->serializeToArray();
        }
        $extension = $this->getExtension();
        $result[self::EXTENSION] = $extension != null ? $extension->serializeToArray() : null;
        $result[self::REFUNDS] = [];
        foreach ($this->getRefunds() as $item) {
            $result[self::REFUNDS][] = $item->serializeToArray();
        }
        $result[self::CARRIERS] = [];
        foreach ($this->getCarriers() as $item) {
            $result[self::CARRIERS][] = $item->serializeToArray();
        }
        $customer = $this->getCustomer();
        $result[self::CUSTOMER] = $customer != null ? $customer->serializeToArray() : null;
        return $result;
    }
}
