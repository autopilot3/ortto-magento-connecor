<?php

namespace Autopilot\AP3Connector\Model\Data;

use Autopilot\AP3Connector\Api\Data\TrackingDataInterface;
use Autopilot\AP3Connector\Helper\To;
use Magento\Framework\DataObject;

class TrackingData extends DataObject implements TrackingDataInterface
{
    /**
     * @inheritDoc
     */
    public function getScopeId(): int
    {
        return To::int($this->getData(self::SCOPE_ID));
    }

    /**
     * @inheritDoc
     */
    public function setScopeId(int $scopeId)
    {
        return $this->setData(self::SCOPE_ID, $scopeId);
    }

    /**
     * @inheritDoc
     */
    public function getScopeType(): string
    {
        return $this->getData(self::SCOPE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setScopeType(string $scopeType)
    {
        return $this->setData(self::SCOPE_TYPE, $scopeType);
    }

    /**
     * @inheritDoc
     */
    public function getEmail(): string
    {
        return (string)$this->getData(self::EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setEmail(string $email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheritDoc
     */
    public function getPhone(): string
    {
        return (string)$this->getData(self::PHONE);
    }

    /**
     * @inheritDoc
     */
    public function setPhone(string $phone)
    {
        return $this->setData(self::PHONE, $phone);
    }
}
