<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

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
     * @param string $category
     * @return bool
     */
    public function isAutoSyncEnabled(string $scopeType, int $scopeId, string $category): bool;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return bool
     */
    public function isNonSubscribedCustomerSyncEnabled(string $scopeType, int $scopeId): bool;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return bool
     */
    public function isAnonymousOrderSyncEnabled(string $scopeType, int $scopeId): bool;

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
    public function getTrackingJsURL(string $scopeType, int $scopeId): string;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return string
     */
    public function getCaptureURL(string $scopeType, int $scopeId): string;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return string
     */
    public function getInstanceId(string $scopeType, int $scopeId): string;
}
