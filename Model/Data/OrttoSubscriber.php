<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoSubscriberInterface;
use Ortto\Connector\Helper\To;

class OrttoSubscriber extends DataObject implements OrttoSubscriberInterface
{

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::SUBSCRIBER_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return To::int($this->getData(self::SUBSCRIBER_ID));
    }

    /**
     * @inheritDoc
     */
    public function getStoreId()
    {
        return To::int($this->getData(self::STORE_ID));
    }

    /**
     * @inheritDoc
     */
    public function setStoreId($id)
    {
        return $this->setData(self::STORE_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return To::int($this->getData(self::CUSTOMER_ID));
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId($id)
    {
        return $this->setData(self::CUSTOMER_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function setEmail($email)
    {
        return $this->setData(self::SUBSCRIBER_EMAIL, $email);
    }

    /**
     * @inheritDoc
     */
    public function getEmail()
    {
        return (string)$this->getData(self::SUBSCRIBER_EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setStatusCode($code)
    {
        switch ($code) {
            case \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED:
                $this->setData(self::STATUS, self::STATUS_SUBSCRIBED);
                break;
            case \Magento\Newsletter\Model\Subscriber::STATUS_UNSUBSCRIBED:
                $this->setData(self::STATUS, self::STATUS_UNSUBSCRIBED);
                break;
            case \Magento\Newsletter\Model\Subscriber::STATUS_UNCONFIRMED:
                $this->setData(self::STATUS, self::STATUS_UNCONFIRMED);
                break;
            case \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE:
                $this->setData(self::STATUS, self::STATUS_INACTIVE);
                break;
            default:
                $this->setData(self::STATUS, self::STATUS_UNKNOWN);
        }
        return $this->setData(self::SUBSCRIBER_STATUS, $code);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return (string)$this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode()
    {
        return To::int($this->getData(self::SUBSCRIBER_STATUS));

    }

    /** @inheirtDoc */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::CHANGE_STATUS_AT, $updatedAt);
    }

    /** @inheirtDoc */
    public function getUpdatedAt()
    {
        return (string)$this->getData(self::CHANGE_STATUS_AT);
    }

    /** @inheirtDoc */
    public function isSubscribed()
    {
        return $this->getStatusCode() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED;
    }

    /**
     * @inheritDoc
     */
    public function serializeToArray()
    {
        if ($this == null) {
            return null;
        }
        $result = [];
        $result[self::SUBSCRIBER_ID] = $this->getId();
        $result[self::STORE_ID] = $this->getStoreId();
        $result[self::CUSTOMER_ID] = $this->getCustomerId();
        $result[self::SUBSCRIBER_EMAIL] = $this->getEmail();
        $result[self::STATUS] = $this->getStatus();
        $result[self::SUBSCRIBER_STATUS] = $this->getStatusCode();
        $result[self::CHANGE_STATUS_AT] = $this->getUpdatedAt();
        $result[self::IS_SUBSCRIBED] = $this->isSubscribed();
        return $result;
    }
}
