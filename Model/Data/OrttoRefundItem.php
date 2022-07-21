<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoRefundItemInterface;
use Ortto\Connector\Helper\To;

class OrttoRefundItem extends DataObject implements OrttoRefundItemInterface
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
    public function setProduct($product)
    {
        return $this->setData(self::PRODUCT, $product);
    }

    /** @inheirtDoc */
    public function getProduct()
    {
        return $this->getData(self::PRODUCT);
    }

    /** @inheirtDoc */
    public function setTotalRefunded($totalRefunded)
    {
        return $this->setData(self::TOTAL_REFUNDED, $totalRefunded);
    }

    /** @inheirtDoc */
    public function getTotalRefunded()
    {
        return To::float($this->getData(self::TOTAL_REFUNDED));
    }

    /** @inheirtDoc */
    public function setBaseTotalRefunded($baseTotalRefunded)
    {
        return $this->setData(self::BASE_TOTAL_REFUNDED, $baseTotalRefunded);
    }

    /** @inheirtDoc */
    public function getBaseTotalRefunded()
    {
        return To::float($this->getData(self::BASE_TOTAL_REFUNDED));
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
    public function setRefundQuantity($refundQuantity)
    {
        return $this->setData(self::REFUND_QUANTITY, $refundQuantity);
    }

    /** @inheirtDoc */
    public function getRefundQuantity()
    {
        return To::float($this->getData(self::REFUND_QUANTITY));
    }

    /** @inheirtDoc */
    public function setOrderItemId($orderItemId)
    {
        return $this->setData(self::ORDER_ITEM_ID, $orderItemId);
    }

    /** @inheirtDoc */
    public function getOrderItemId()
    {
        return To::int($this->getData(self::ORDER_ITEM_ID));
    }

    /** @inheirtDoc */
    public function serializeToArray()
    {
        if ($this == null) {
            return null;
        }
        $result=[];
        $result[self::ID] = $this->getId();
        $product = $this->getProduct();
        $result[self::PRODUCT] = $product != null ? $product->serializeToArray() : null;
        $result[self::TOTAL_REFUNDED] = $this->getTotalRefunded();
        $result[self::BASE_TOTAL_REFUNDED] = $this->getBaseTotalRefunded();
        $result[self::DISCOUNT_REFUNDED] = $this->getDiscountRefunded();
        $result[self::BASE_DISCOUNT_REFUNDED] = $this->getBaseDiscountRefunded();
        $result[self::REFUND_QUANTITY] = $this->getRefundQuantity();
        $result[self::ORDER_ITEM_ID] = $this->getOrderItemId();
        return $result;
    }
}
