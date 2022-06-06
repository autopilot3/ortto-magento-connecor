<?php
declare(strict_types=1);


namespace Ortto\Connector\Block;

use Ortto\Connector\Api\ConfigurationReaderInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Framework\View\Element\Template;
use Exception;
use Magento\Store\Model\ScopeInterface;

class HeaderJs extends Template
{
    public const TRACKING_CODE = 'c';
    public const MAGENTO_JS = 'mgj';
    public const CAPTURE_JS = 'cj';
    public const CAPTURE_API = 'ca';

    private ConfigurationReaderInterface $configReader;
    private OrttoLogger $logger;
    private ScopeManagerInterface $scopeManager;

    public function __construct(
        Template\Context $context,
        ConfigurationReaderInterface $configReader,
        ScopeManagerInterface $scopeManager,
        OrttoLogger $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configReader = $configReader;
        $this->logger = $logger;
        $this->scopeManager = $scopeManager;
    }

    public function getConfiguration(): array
    {
        try {
            $store = $this->_storeManager->getStore();
            $storeId = To::int($store->getId());
            $scope = $this->scopeManager->initialiseScope(ScopeInterface::SCOPE_STORE, $storeId);
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to get current store details");
            return [];
        }

        $enabled = $scope->isConnected()
            && $this->configReader->isTrackingEnabled(ScopeInterface::SCOPE_STORE, $storeId);
        if (!$enabled) {
            return [];
        }

        $captureJS = $this->configReader->getCaptureJsURL(ScopeInterface::SCOPE_STORE, $storeId);
        if (empty($captureJS)) {
            return [];
        }

        $magentoJS = $this->configReader->getMagentoCaptureJsURL(ScopeInterface::SCOPE_STORE, $storeId);
        if (empty($magentoJS)) {
            return [];
        }

        $captureURL = $this->configReader->getCaptureApiURL(ScopeInterface::SCOPE_STORE, $storeId);
        if (empty($captureURL)) {
            return [];
        }

        $code = $this->configReader->getTrackingCode(ScopeInterface::SCOPE_STORE, $storeId);
        if (empty($code)) {
            return [];
        }

        return [
            self::TRACKING_CODE => $code,
            self::CAPTURE_API => $captureURL,
            self::CAPTURE_JS => $captureJS,
            self::MAGENTO_JS => $magentoJS,
        ];
    }
}
