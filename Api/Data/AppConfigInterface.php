<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api\Data;

/**
 * Interface AppConfigInterface
 * @api
 */
interface AppConfigInterface extends SerializableInterface
{
    public const TRACKING_CODE = 'tracking_code';
    public const TRACKING_JS_URL = 'tracking_js_url';
    public const CAPTURE_URL = 'capture_url';
    public const INSTANCE_ID = 'instance_id';
    public const SCOPE_ID = 'scope_id';
    public const SCOPE_TYPE = 'scope_type';

    /**
     * @param string $instanceId
     * @return $this
     */
    public function setInstanceId(string $instanceId): AppConfigInterface;

    /**
     * @return string
     */
    public function getInstanceId(): string;

    /**
     * @param string $trackingCode
     * @return $this
     */
    public function setTrackingCode(string $trackingCode): AppConfigInterface;

    /**
     * @return string
     */
    public function getTrackingCode(): string;

    /**
     * @param string $trackingURL
     * @return $this
     */
    public function setTrackingJsUrl(string $trackingURL): AppConfigInterface;

    /**
     * @return string
     */
    public function getTrackingJsUrl(): string;

    /**
     * @param string $url
     * @return $this
     */
    public function setCaptureUrl(string $url): AppConfigInterface;

    /**
     * @return string
     */
    public function getCaptureUrl(): string;

    /**
     * @param int $scopeId
     * @return $this
     */
    public function setScopeId(int $scopeId): AppConfigInterface;

    /**
     * @return int
     */
    public function getScopeId(): int;

    /**
     * @param string $scopeType
     * @return $this
     */
    public function setScopeType(string $scopeType): AppConfigInterface;

    /**
     * @return string
     */
    public function getScopeType(): string;
}
