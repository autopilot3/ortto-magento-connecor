<?php

namespace Ortto\Connector\Model\Data;

use Ortto\Connector\Api\ConfigScopeInterface;
use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\TrackingOptionsInterface;
use Ortto\Connector\Helper\To;

class TrackingOptions extends DataObject implements TrackingOptionsInterface
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
    public function setCaptureAPI(string $url)
    {
        return $this->setData(self::CAPTURE_API, $url);
    }

    /**
     * @inheritDoc
     */
    public function getCaptureAPI(): string
    {
        return $this->getData(self::CAPTURE_API);
    }

    /**
     * @inheritDoc
     */
    public function setCaptureJS(string $url)
    {
        return $this->setData(self::CAPTURE_JS, $url);
    }

    /**
     * @inheritDoc
     */
    public function getCaptureJS(): string
    {
        return $this->getData(self::CAPTURE_JS);
    }

    /**
     * @inheritDoc
     */
    public function setMagentoJS(string $url)
    {
        return $this->setData(self::MAGENTO_JS, $url);
    }

    /**
     * @inheritDoc
     */
    public function getMagentoJS(): string
    {
        return $this->getData(self::MAGENTO_JS);
    }

    /**
     * @inheritDoc
     */
    public function setTrackingCode(string $code)
    {
        return $this->setData(self::TRACKING_CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getTrackingCode(): string
    {
        return $this->getData(self::TRACKING_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setNeedsConsent(bool $required)
    {
        return $this->setData(self::NEEDS_CONSENT, $required);
    }

    /**
     * @inheritDoc
     */
    public function getNeedsConsent(): bool
    {
        return To::bool($this->getData(self::NEEDS_CONSENT));
    }
}
