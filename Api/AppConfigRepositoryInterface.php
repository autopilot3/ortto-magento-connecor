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
}
