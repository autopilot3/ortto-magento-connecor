<?php

namespace Autopilot\AP3Connector\Api;

interface ScopeManagerInterface
{
    /**
     * @return ConfigScopeInterface[]
     */
    public function getActiveScopes(): array;

    /**
     * @param string $scopeType
     * @param int|null $scopeId
     * @return ConfigScopeInterface
     */
    public function getCurrentConfigurationScope(string $scopeType = '', int $scopeId = null): ConfigScopeInterface;
}
