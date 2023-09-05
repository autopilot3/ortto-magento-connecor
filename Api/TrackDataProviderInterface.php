<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\TrackingDataInterface;

interface TrackDataProviderInterface
{
    public function getData(): TrackingDataInterface;
}
