<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoCustomerInterface;
use Ortto\Connector\Helper\To;

class OrttoCustomer extends DataObject implements OrttoCustomerInterface
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
    public function setFirstName($firstName)
    {
        return $this->setData(self::FIRST_NAME, $firstName);
    }

    /** @inheirtDoc */
    public function getFirstName()
    {
        return (string)$this->getData(self::FIRST_NAME);
    }

    /** @inheirtDoc */
    public function setMiddleName($middleName)
    {
        return $this->setData(self::MIDDLE_NAME, $middleName);
    }

    /** @inheirtDoc */
    public function getMiddleName()
    {
        return (string)$this->getData(self::MIDDLE_NAME);
    }

    /** @inheirtDoc */
    public function setLastName($lastName)
    {
        return $this->setData(self::LAST_NAME, $lastName);
    }

    /** @inheirtDoc */
    public function getLastName()
    {
        return (string)$this->getData(self::LAST_NAME);
    }

    /** @inheirtDoc */
    public function setSuffix($suffix)
    {
        return $this->setData(self::SUFFIX, $suffix);
    }

    /** @inheirtDoc */
    public function getSuffix()
    {
        return (string)$this->getData(self::SUFFIX);
    }

    /** @inheirtDoc */
    public function setPrefix($prefix)
    {
        return $this->setData(self::PREFIX, $prefix);
    }

    /** @inheirtDoc */
    public function getPrefix()
    {
        return (string)$this->getData(self::PREFIX);
    }

    /** @inheirtDoc */
    public function setGender($gender)
    {
        return $this->setData(self::GENDER, $gender);
    }

    /** @inheirtDoc */
    public function getGender()
    {
        return (string)$this->getData(self::GENDER);
    }

    /** @inheirtDoc */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /** @inheirtDoc */
    public function getEmail()
    {
        return (string)$this->getData(self::EMAIL);
    }

    /** @inheirtDoc */
    public function setDateOfBirth($dateOfBirth)
    {
        return $this->setData(self::DOB, $dateOfBirth);
    }

    /** @inheirtDoc */
    public function getDateOfBirth()
    {
        return (string)$this->getData(self::DOB);
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
    public function setCreatedIn($createdIn)
    {
        return $this->setData(self::CREATED_IN, $createdIn);
    }

    /** @inheirtDoc */
    public function getCreatedIn()
    {
        return (string)$this->getData(self::CREATED_IN);
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
    public function setPhone($phone)
    {
        return $this->setData(self::PHONE, $phone);
    }

    /** @inheirtDoc */
    public function getPhone()
    {
        return (string)$this->getData(self::PHONE);
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
        $result[self::FIRST_NAME] = $this->getFirstName();
        $result[self::MIDDLE_NAME] = $this->getMiddleName();
        $result[self::LAST_NAME] = $this->getLastName();
        $result[self::SUFFIX] = $this->getSuffix();
        $result[self::PREFIX] = $this->getPrefix();
        $result[self::GENDER] = $this->getGender();
        $result[self::EMAIL] = $this->getEmail();
        $result[self::DOB] = $this->getDateOfBirth();
        $result[self::CREATED_AT] = $this->getCreatedAt();
        $result[self::CREATED_IN] = $this->getCreatedIn();
        $billingAddress = $this->getBillingAddress();
        $result[self::BILLING_ADDRESS] = $billingAddress != null ? $billingAddress->serializeToArray() : null;
        $shippingAddress = $this->getShippingAddress();
        $result[self::SHIPPING_ADDRESS] = $shippingAddress != null ? $shippingAddress->serializeToArray() : null;
        $result[self::UPDATED_AT] = $this->getUpdatedAt();
        $result[self::PHONE] = $this->getPhone();
        return $result;
    }
}
