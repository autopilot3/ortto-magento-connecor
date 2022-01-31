<?php


namespace Autopilot\AP3Connector\Logger;


use Autopilot\AP3Connector\Model\AutopilotException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class Logger
{
    const XML_PATH_DEBUG_LOG_ENABLED = "autopilot/general/debug_logs";

    private LoggerInterface $logger;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(LoggerInterface $logger, ScopeConfigInterface $scopeConfig)
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    public function info(string $message, $data = null)
    {
        $this->logger->info($message, ['data' => $this->encodeData($data)]);
    }

    public function error(\Exception $exception)
    {
        $params = [
            'code' => $exception->getCode(),
        ];
        if ($exception instanceof AutopilotException) {
            $params['url'] = $exception->getUrl();
            $params['method'] = $exception->getMethod();
            $params['data'] = $this->encodeData($exception->getParams());
            $internalErr = $exception->getError();
            if ($internalErr !== null) {
                $params['err'] = $internalErr->getMessage();
            }
        }
        $this->logger->error($exception->getMessage(), $params);
    }

    public function debug(string $message, ?int $storeId, $data = null)
    {
        if ($storeId === null) {
            return;
        }

        $enabled = $this->scopeConfig->getValue(self::XML_PATH_DEBUG_LOG_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
        if ($enabled) {
            $this->logger->debug($message, ['data' => $this->encodeData($data)]);
        }
    }


    private function encodeData($data): string
    {
        if ($data == null) {
            return '';
        }

        try {
            if (is_array($data) || is_object($data)) {
                return json_encode($data);
            }
            if (is_string($data)) {
                return (string)$data;
            }
        } catch (\Exception $e) {
            return "Failed to encode data: " . $e->getMessage();
        }
        return '';
    }
}

