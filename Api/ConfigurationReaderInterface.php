<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface ConfigurationReaderInterface
{
    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return bool
     */
    public function isActive(string $scopeType, int $scopeId): bool;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return bool
     */
    public function isTrackingEnabled(string $scopeType, int $scopeId): bool;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return bool
     */
    public function verboseLogging(string $scopeType, int $scopeId): bool;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return string
     */
    public function getAPIKey(string $scopeType, int $scopeId): string;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return array
     */
    public function getPlaceholderImages(string $scopeType, int $scopeId): array;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return string
     */
    public function getTrackingCode(string $scopeType, int $scopeId): string;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return string
     */
    public function getCaptureJsURL(string $scopeType, int $scopeId): string;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return string
     */
    public function getMagentoCaptureJsURL(string $scopeType, int $scopeId): string;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return string
     */
    public function getCaptureApiURL(string $scopeType, int $scopeId): string;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return string
     */
    public function getInstanceId(string $scopeType, int $scopeId): string;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return array
     */
    public function getAllConfigs(string $scopeType, int $scopeId): array;
}
