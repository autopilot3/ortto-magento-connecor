<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Api\Data\TrackingDataInterface;

interface TrackDataProviderInterface
{
    public function getData(): TrackingDataInterface;
}
