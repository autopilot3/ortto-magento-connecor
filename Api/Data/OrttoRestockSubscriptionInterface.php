<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoRestockSubscriptionInterface
{
    const PRODUCT = 'product';
    const CUSTOMER = 'customer';
    const DATE_ADDED = 'date_added';

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
    * Set date added
    *
    * @param string $dateAdded
    * @return $this
    */
    public function setDateAdded($dateAdded);

    /**
    * Get date added
    *
    * @return string
    */
    public function getDateAdded();
}
