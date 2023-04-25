<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\AppConfigInterface;

/**
 *  Interface AppConfigRepositoryInterface
 * @api
 */
interface AppConfigRepositoryInterface
{
    /**
     * @param AppConfigInterface $config
     * @return void
     */
    public function update(AppConfigInterface $config);

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @return array
     */
    public function get(string $scopeType, int $scopeId);

    /**
     * @return array
     */
    public function getAllStoreConfigs(): array;
}
