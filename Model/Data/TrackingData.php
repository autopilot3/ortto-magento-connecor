<?php

namespace Ortto\Connector\Model\Data;

use Ortto\Connector\Api\Data\TrackingDataInterface;
use Ortto\Connector\Helper\To;
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
        $value = $this->getData(self::SCOPE_TYPE);
        return $value ? (string)$value : '';
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
        $value = $this->getData(self::EMAIL);
        return $value ? (string)$value : '';
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
        $value = $this->getData(self::PHONE);
        return $value ? (string)$value : '';
    }

    /**
     * @inheritDoc
     */
    public function setPhone(string $phone)
    {
        return $this->setData(self::PHONE, $phone);
    }
}
