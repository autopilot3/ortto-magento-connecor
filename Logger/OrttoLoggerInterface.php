<?php
declare(strict_types=1);

namespace Ortto\Connector\Logger;

use Exception;

interface OrttoLoggerInterface
{
    public function info(string $message, $data = null);

    public function warn(string $message, $data = null);

    public function error(Exception $exception, string $message = '');

    public function debug(string $message, $data = null);
}
