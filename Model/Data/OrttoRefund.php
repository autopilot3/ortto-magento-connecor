<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoRefundInterface;
use Ortto\Connector\Helper\To;

class OrttoRefund extends DataObject implements OrttoRefundInterface
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
    public function setInvoiceId($invoiceId)
    {
        return $this->setData(self::INVOICE_ID, $invoiceId);
    }

    /** @inheirtDoc */
    public function getInvoiceId()
    {
        return To::int($this->getData(self::INVOICE_ID));
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
    public function setAdjustment($adjustment)
    {
        return $this->setData(self::ADJUSTMENT, $adjustment);
    }

    /** @inheirtDoc */
    public function getAdjustment()
    {
        return To::float($this->getData(self::ADJUSTMENT));
    }

    /** @inheirtDoc */
    public function setBaseAdjustment($baseAdjustment)
    {
        return $this->setData(self::BASE_ADJUSTMENT, $baseAdjustment);
    }

    /** @inheirtDoc */
    public function getBaseAdjustment()
    {
        return To::float($this->getData(self::BASE_ADJUSTMENT));
    }

    /** @inheirtDoc */
    public function setRefundedAt($refundedAt)
    {
        return $this->setData(self::REFUNDED_AT, $refundedAt);
    }

    /** @inheirtDoc */
    public function getRefundedAt()
    {
        return (string)$this->getData(self::REFUNDED_AT);
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
    public function serializeToArray()
    {
        if ($this == null) {
            return null;
        }
        $result=[];
        $result[self::ID] = $this->getId();
        $result[self::INVOICE_ID] = $this->getInvoiceId();
        $result[self::NUMBER] = $this->getNumber();
        $result[self::SUBTOTAL] = $this->getSubtotal();
        $result[self::BASE_SUBTOTAL] = $this->getBaseSubtotal();
        $result[self::SUBTOTAL_INCL_TAX] = $this->getSubtotalInclTax();
        $result[self::BASE_SUBTOTAL_INCL_TAX] = $this->getBaseSubtotalInclTax();
        $result[self::TAX] = $this->getTax();
        $result[self::BASE_TAX] = $this->getBaseTax();
        $result[self::SHIPPING] = $this->getShipping();
        $result[self::BASE_SHIPPING] = $this->getBaseShipping();
        $result[self::SHIPPING_INCL_TAX] = $this->getShippingInclTax();
        $result[self::BASE_SHIPPING_INCL_TAX] = $this->getBaseShippingInclTax();
        $result[self::GRAND_TOTAL] = $this->getGrandTotal();
        $result[self::BASE_GRAND_TOTAL] = $this->getBaseGrandTotal();
        $result[self::ADJUSTMENT] = $this->getAdjustment();
        $result[self::BASE_ADJUSTMENT] = $this->getBaseAdjustment();
        $result[self::REFUNDED_AT] = $this->getRefundedAt();
        $result[self::DISCOUNT] = $this->getDiscount();
        $result[self::ITEMS] = [];
        foreach ($this->getItems() as $item) {
            $result[self::ITEMS][] = $item->serializeToArray();
        }
        return $result;
    }
}
