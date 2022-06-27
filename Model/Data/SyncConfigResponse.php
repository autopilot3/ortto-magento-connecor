<?php

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\SyncConfigResponseInterface;
use Ortto\Connector\Helper\To;

class SyncConfigResponse extends DataObject implements SyncConfigResponseInterface
{
    /**
     * Getter for Product.
     *
     * @return bool
     */
    public function getProduct(): bool
    {
        return To::bool($this->getData(self::PRODUCT));
    }

    /** @inheirtDoc */
    public function setProduct(bool $product): void
    {
        $this->setData(self::PRODUCT, $product);
    }

    /**
     * Getter for Order.
     *
     * @return bool
     */
    public function getOrder(): bool
    {
        return To::bool($this->getData(self::ORDER));
    }

    /**
     * Setter for Order.
     *
     * @param bool $order
     *
     * @return void
     */
    public function setOrder(bool $order): void
    {
        $this->setData(self::ORDER, $order);
    }

    /**
     * Getter for StockAlert.
     *
     * @return bool
     */
    public function getStockAlert(): bool
    {
        return To::bool($this->getData(self::STOCK_ALERT));
    }

    /**
     * Setter for StockAlert.
     *
     * @param bool $stockAlert
     *
     * @return void
     */
    public function setStockAlert(bool $stockAlert): void
    {
        $this->setData(self::STOCK_ALERT, $stockAlert);
    }

    /**
     * Getter for Customer.
     *
     * @return bool
     */
    public function getCustomer(): bool
    {
        return To::bool($this->getData(self::CUSTOMER));
    }

    /**
     * Setter for Customer.
     *
     * @param bool $customer
     *
     * @return void
     */
    public function setCustomer(bool $customer): void
    {
        $this->setData(self::CUSTOMER, $customer);
    }

    /**
     * Getter for Coupon.
     *
     * @return bool
     */
    public function getCoupon(): bool
    {
        return To::bool($this->getData(self::COUPON));
    }

    /**
     * Setter for Coupon.
     *
     * @param bool $coupon
     *
     * @return void
     */
    public function setCoupon(bool $coupon): void
    {
        $this->setData(self::COUPON, $coupon);
    }

    /**
     * Getter for Review.
     *
     * @return bool
     */
    public function getReview(): bool
    {
        return To::bool($this->getData(self::REVIEW));
    }

    /**
     * Setter for Review.
     *
     * @param bool $review
     *
     * @return void
     */
    public function setReview(bool $review): void
    {
        $this->setData(self::REVIEW, $review);
    }
}
