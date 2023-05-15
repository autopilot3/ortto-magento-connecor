<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoCustomerInterface
{
    const ID = 'id';
    const IP_ADDRESS = 'ip_address';
    const FIRST_NAME = 'first_name';
    const MIDDLE_NAME = 'middle_name';
    const LAST_NAME = 'last_name';
    const SUFFIX = 'suffix';
    const PREFIX = 'prefix';
    const GENDER = 'gender';
    const EMAIL = 'email';
    const DATE_OF_BIRTH = 'date_of_birth';
    const CREATED_AT = 'created_at';
    const CREATED_IN = 'created_in';
    const BILLING_ADDRESS = 'billing_address';
    const SHIPPING_ADDRESS = 'shipping_address';
    const UPDATED_AT = 'updated_at';
    const PHONE = 'phone';

    const IS_SUBSCRIBED = 'is_subscribed';
    const STORE = 'store';

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
     * Set Newsletter subscription state
     *
     * @param bool $subscribed
     * @return $this
     */
    public function setIsSubscribed($subscribed);

    /**
     * Get Newsletter subscription level
     *
     * @return bool
     */
    public function getIsSubscribed();

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
     * Set first name
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName);

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Set middle name
     *
     * @param string $middleName
     * @return $this
     */
    public function setMiddleName($middleName);

    /**
     * Get middle name
     *
     * @return string
     */
    public function getMiddleName();

    /**
     * Set last name
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName);

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName();

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix);

    /**
     * Get suffix
     *
     * @return string
     */
    public function getSuffix();

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Get prefix
     *
     * @return string
     */
    public function getPrefix();

    /**
     * Set gender
     *
     * @param string $gender
     * @return $this
     */
    public function setGender($gender);

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set date of birth
     *
     * @param string $dateOfBirth
     * @return $this
     */
    public function setDateOfBirth($dateOfBirth);

    /**
     * Get date of birth
     *
     * @return string
     */
    public function getDateOfBirth();

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
     * Set created in
     *
     * @param string $createdIn
     * @return $this
     */
    public function setCreatedIn($createdIn);

    /**
     * Get created in
     *
     * @return string
     */
    public function getCreatedIn();

    /**
     * Set billing address
     *
     * @param \Ortto\Connector\Api\Data\OrttoAddressInterface $billingAddress
     * @return $this
     */
    public function setBillingAddress($billingAddress);

    /**
     * Get billing address
     *
     * @return \Ortto\Connector\Api\Data\OrttoAddressInterface
     */
    public function getBillingAddress();

    /**
     * Set shipping address
     *
     * @param \Ortto\Connector\Api\Data\OrttoAddressInterface $shippingAddress
     * @return $this
     */
    public function setShippingAddress($shippingAddress);

    /**
     * Get shipping address
     *
     * @return \Ortto\Connector\Api\Data\OrttoAddressInterface
     */
    public function getShippingAddress();

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
     * Set phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone);

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone();

    /**
     * Set store
     *
     * @param \Ortto\Connector\Api\Data\OrttoStoreInterface $store
     * @return $this
     */
    public function setStore($store);

    /**
     * Get store
     *
     * @return \Ortto\Connector\Api\Data\OrttoStoreInterface|null
     */
    public function getStore();

    /**
     * Convert object data to array
     *
     * @return array
     */
    public function serializeToArray();
}
