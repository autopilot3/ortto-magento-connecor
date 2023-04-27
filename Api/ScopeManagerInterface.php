<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

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

    /**
     * @param string $type
     * @param int $id
     * @return ConfigScopeInterface
     * @throws InvalidArgumentException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function initialiseScope(
        string $type,
        int $id
    );

    /**
     * @return  ConfigScopeInterface[]
     */
    public function getAllScopes();
}
