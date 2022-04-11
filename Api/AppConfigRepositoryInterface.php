<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Api\Data\AppConfigInterface;

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
