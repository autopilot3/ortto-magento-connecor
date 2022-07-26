<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoProductReviewInterface
{
    const CREATED_AT = 'created_at';
    const NICKNAME = 'nickname';
    const DETAILS = 'details';
    const TITLE = 'title';
    const STATUS = 'status';
    const PRODUCT = 'product';
    const CUSTOMER = 'customer';

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
    * Set nickname
    *
    * @param string $nickname
    * @return $this
    */
    public function setNickname($nickname);

    /**
    * Get nickname
    *
    * @return string
    */
    public function getNickname();

    /**
    * Set details
    *
    * @param string $details
    * @return $this
    */
    public function setDetails($details);

    /**
    * Get details
    *
    * @return string
    */
    public function getDetails();

    /**
    * Set title
    *
    * @param string $title
    * @return $this
    */
    public function setTitle($title);

    /**
    * Get title
    *
    * @return string
    */
    public function getTitle();

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
    * Convert object data to array
    *
    * @return array
    */
    public function serializeToArray();
}
