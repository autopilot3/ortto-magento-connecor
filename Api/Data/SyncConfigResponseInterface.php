<?php

namespace Ortto\Connector\Api\Data;

interface SyncConfigResponseInterface
{
    /**
     * String constants for property names
     */
    const PRODUCT = "product";
    const ORDER = "order";
    const STOCK_ALERT = "stock_alert";
    const CUSTOMER = "customer";
    const COUPON = "coupon";
    const REVIEW = "review";

    /**
     * Getter for Product.
     *
     * @return bool
     */
    public function getProduct(): bool;

    /**
     * Setter for Product.
     *
     * @param bool $product
     *
     * @return void
     */
    public function setProduct(bool $product): void;

    /**
     * Getter for Order.
     *
     * @return bool
     */
    public function getOrder(): bool;

    /**
     * Setter for Order.
     *
     * @param bool $order
     *
     * @return void
     */
    public function setOrder(bool $order): void;

    /**
     * Getter for StockAlert.
     *
     * @return bool
     */
    public function getStockAlert(): bool;

    /**
     * Setter for StockAlert.
     *
     * @param bool $stockAlert
     *
     * @return void
     */
    public function setStockAlert(bool $stockAlert): void;

    /**
     * Getter for Customer.
     *
     * @return bool
     */
    public function getCustomer(): bool;

    /**
     * Setter for Customer.
     *
     * @param bool $customer
     *
     * @return void
     */
    public function setCustomer(bool $customer): void;

    /**
     * Getter for Coupon.
     *
     * @return bool
     */
    public function getCoupon(): bool;

    /**
     * Setter for Coupon.
     *
     * @param bool $coupon
     *
     * @return void
     */
    public function setCoupon(bool $coupon): void;

    /**
     * Getter for Review.
     *
     * @return bool
     */
    public function getReview(): bool;

    /**
     * Setter for Review.
     *
     * @param bool $review
     *
     * @return void
     */
    public function setReview(bool $review): void;
}
