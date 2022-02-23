<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Logger;

use Exception;

interface AutopilotLoggerInterface
{
    public function info(string $message, $data = null);

    public function warn(string $message, $data = null);

    public function error(Exception $exception, string $message = '');

    public function debug(string $message, $data = null);
}
