<?php

namespace Ortto\Connector\Model\Data;

use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\Data\TrackingDataInterface;
use Magento\Framework\DataObject;
use Ortto\Connector\Helper\To;

class TrackingData extends DataObject implements TrackingDataInterface
{
    /**
     * @inheritDoc
     */
    public function getScope(): ConfigScopeInterface
    {
        return $this->getData(self::SCOPE);
    }

    /**
     * @inheritDoc
     */
    public function setScope(ConfigScopeInterface $scope)
    {
        return $this->setData(self::SCOPE, $scope);
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

    /**
     * @inheritDoc
     */
    public function isTrackingEnabled(): bool
    {
        return To::bool($this->getData(self::ENABLED));
    }

    /**
     * @inheritDoc
     */
    public function setEnabled(bool $enabled)
    {
        return $this->setData(self::ENABLED, $enabled);
    }
}
