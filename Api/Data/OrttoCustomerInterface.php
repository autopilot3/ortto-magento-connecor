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
    const DOB = 'dob';
    const CREATED_AT = 'created_at';
    const CREATED_IN = 'created_in';
    const GROUP = 'group';
    const BILLING_ADDRESS = 'billing_address';
    const SHIPPING_ADDRESS = 'shipping_address';
    const UPDATED_AT = 'updated_at';
    const IS_SUBSCRIBED = 'is_subscribed';
    const PHONE = 'phone';

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
    * Set dob
    *
    * @param string $dateOfBirth
    * @return $this
    */
    public function setDateOfBirth($dateOfBirth);

    /**
    * Get dob
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
    * Set group
    *
    * @param string $group
    * @return $this
    */
    public function setGroup($group);

    /**
    * Get group
    *
    * @return string
    */
    public function getGroup();

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
    * Set is subscribed
    *
    * @param bool $isSubscribed
    * @return $this
    */
    public function setIsSubscribed($isSubscribed);

    /**
    * Get is subscribed
    *
    * @return bool
    */
    public function getIsSubscribed();

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
}
