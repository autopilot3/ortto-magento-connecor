<?php

namespace Autopilot\AP3Connector\Logger;

interface AutopilotLoggerInterface
{
    public function info(string $message, $data = null);

    public function warn(string $message, $data = null);

    public function error(\Exception $exception, string $message = '');

    public function debug(string $message, $data = null);
}
