<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\ResourceModel;

use Ortto\Connector\Api\AppConfigRepositoryInterface;
use Ortto\Connector\Api\Data\AppConfigInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class AppConfigRepository implements AppConfigRepositoryInterface
{
    private OrttoLoggerInterface $logger;
    private WriterInterface $configWriter;
    private StoreManagerInterface $storeManager;
    private Pool $cacheFrontendPool;
    private array $validKeys = [
        'instance_id' => Config::XML_PATH_INSTANCE_ID,
        'data_source_id' => Config::XML_PATH_DATA_SOURCE_ID,
        'capture_js_url' => Config::XML_PATH_CAPTURE_JS_URL,
        'capture_api_url' => Config::XML_PATH_CAPTURE_API_URL,
        'magento_capture_js_url' => Config::XML_PATH_MAGENTO_CAPTURE_JS_URL,
        'tracking_code' => Config::XML_PATH_TRACKING_CODE,
        'enable' => Config::XML_PATH_ACTIVE,
        'enable_tracking' => Config::XML_PATH_TRACKING_ENABLED,
    ];

    /**
     * @param OrttoLoggerInterface $logger
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        OrttoLoggerInterface $logger,
        WriterInterface $configWriter,
        StoreManagerInterface $storeManager,
        Pool $cacheFrontendPool
    ) {
        $this->logger = $logger;
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * @throws Exception
     * @throws LocalizedException
     */
    public function update(AppConfigInterface $config)
    {
        $this->logger->debug("Configuration Request Received", $config->toArray());

        $keys = $config->getKeys();
        if (empty($keys)) {
            throw new Exception(__('Requested keys cannot be empty'), 400);
        }

        $toChange = [];
        foreach ($keys as $key => $value) {
            $configKey = $this->validKeys[$key];
            if (empty($configKey)) {
                throw new Exception(__(sprintf(
                    'Invalid configuration key %s. Acceptable keys are: %s',
                    $key,
                    implode(',', array_keys($this->validKeys))
                )), 400);
            }
            if ($key == 'enable' || $key == 'enable_tracking') {
                $value = To::bool($value) ? '1' : '0';
            }
            $toChange[$configKey] = $value;
        }

        $scopeType = $config->getScopeType();
        $scopeId = $config->getScopeId();
        switch (strtolower($scopeType)) {
            case ScopeInterface::SCOPE_WEBSITE:
                // Throws 404 if website was not found
                $this->storeManager->getWebsite($scopeId);
                $scopeType = ScopeInterface::SCOPE_WEBSITES;
                break;
            case ScopeInterface::SCOPE_STORE:
                // Throws 404 if store was not found
                $this->storeManager->getStore($scopeId);
                $scopeType = ScopeInterface::SCOPE_STORES;
                break;
            default:
                throw new Exception(__(sprintf('Invalid scope type %s', $scopeType)), 400);
        }

        foreach ($toChange as $key => $value) {
            $this->configWriter->save(
                $key,
                $value,
                $scopeType,
                $scopeId
            );
        }

        $this->cacheFrontendPool->get('config')->clean();
    }
}
