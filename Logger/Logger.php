<?php
declare(strict_types=1);

namespace Ortto\Connector\Logger;

use Ortto\Connector\Model\OrttoException;
use Exception;
use Psr\Log\LoggerInterface;

class Logger implements OrttoLoggerInterface
{
    private LoggerInterface $logger;

    const LOG_LEVEL_INFO = "info";
    const LOG_LEVEL_WARNING = "warning";
    const LOG_LEVEL_ERROR = "error";
    const LOG_LEVEL_DEBUG = "debug";

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function info(string $message, $data = null)
    {
        $this->log(self::LOG_LEVEL_INFO, $message, $data);
    }

    public function warn(string $message, $data = null)
    {
        $this->log(self::LOG_LEVEL_WARNING, $message, $data);
    }

    public function debug(string $message, $data = null)
    {
        $this->log(self::LOG_LEVEL_DEBUG, $message, $data);
    }

    public function error(Exception $exception, string $message = '')
    {
        $params = [
            'code' => $exception->getCode(),
        ];
        if ($exception instanceof OrttoException) {
            $params = $exception->toArray();
        }
        $msg = $exception->getMessage();
        if (!empty($message)) {
            $msg = $message . '. ' . $msg;
        }

        $this->log(self::LOG_LEVEL_ERROR, $msg, $params);
    }

    private function log(string $level, string $message, $data = null)
    {
        if (empty($data)) {
            $this->logger->log($level, $message);
            return;
        }
        if (is_array($data)) {
            $this->logger->log($level, $message, $data);
            return;
        }
        $this->logger->log($level, $message, ['context' => $this->encodeData($data)]);
    }

    private function encodeData($data)
    {
        try {
            if (is_object($data)) {
                return json_encode($data);
            }
            if (is_string($data)) {
                return (string)$data;
            }
        } catch (Exception $e) {
            return "Failed to encode data: " . $e->getMessage();
        }
        return '';
    }
}
