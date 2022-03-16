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
     * @return string
     */
    public function getAccessToken(string $scopeType, int $scopeId): string;
}
