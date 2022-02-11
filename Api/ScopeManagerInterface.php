<?php

namespace Autopilot\AP3Connector\Api;

interface ScopeManagerInterface
{
    /**
     * @param int|null $websiteId
     * @param int|null $storeId
     * @return ConfigScopeInterface[]
     */
    public function getActiveScopes(?int $websiteId = null, ?int $storeId = null): array;

    /**
     * @param string $scopeType
     * @param int|null $scopeId
     * @return ConfigScopeInterface
     */
    public function getCurrentConfigurationScope(string $scopeType = '', int $scopeId = null): ConfigScopeInterface;
}
