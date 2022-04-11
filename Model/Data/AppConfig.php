<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model\Data;

use Autopilot\AP3Connector\Api\Data\AppConfigInterface;
use Autopilot\AP3Connector\Helper\To;
use Magento\Framework\DataObject;

class AppConfig extends DataObject implements AppConfigInterface
{
    /**
     * @inheritDoc
     */
    public function setInstanceId(string $instanceId): AppConfigInterface
    {
        return $this->setData(self::INSTANCE_ID, $instanceId);
    }

    /**
     * @inheritDoc
     */
    public function getInstanceId(): string
    {
        return trim((string)$this->_getData(self::INSTANCE_ID));
    }

    /**
     * @inheritDoc
     */
    public function setTrackingCode($trackingCode): AppConfigInterface
    {
        return $this->setData(self::TRACKING_CODE, $trackingCode);
    }

    /**
     * @inheritDoc
     */
    public function getTrackingCode(): string
    {
        return trim((string)$this->_getData(self::TRACKING_CODE));
    }

    /**
     * @inheritDoc
     */
    public function setTrackingJsUrl(string $trackingURL): AppConfigInterface
    {
        return $this->setData(self::TRACKING_JS_URL, $trackingURL);
    }

    /**
     * @inheritDoc
     */
    public function getTrackingJsUrl(): string
    {
        return trim((string)$this->_getData(self::TRACKING_JS_URL));
    }

    /**
     * @inheritDoc
     */
    public function setCaptureUrl(string $url): AppConfigInterface
    {
        return $this->setData(self::CAPTURE_URL, $url);
    }

    /**
     * @inheritDoc
     */
    public function getCaptureUrl(): string
    {
        return (string)$this->_getData(self::CAPTURE_URL);
    }

    /**
     * @inheritDoc
     */
    public function setScopeId(int $scopeId): AppConfigInterface
    {
        return $this->setData(self::SCOPE_ID, $scopeId);
    }

    /**
     * @inheritDoc
     */
    public function getScopeId(): int
    {
        return To::int($this->_getData(self::SCOPE_ID));
    }

    /**
     * @inheritDoc
     */
    public function setScopeType(string $scopeType): AppConfigInterface
    {
        return $this->setData(self::SCOPE_TYPE, $scopeType);
    }

    /**
     * @inheritDoc
     */
    public function getScopeType(): string
    {
        return trim((string)$this->_getData(self::SCOPE_TYPE));
    }
}
