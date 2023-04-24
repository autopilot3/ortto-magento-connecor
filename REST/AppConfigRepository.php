<?php
declare(strict_types=1);

namespace Ortto\Connector\REST;

use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ortto\Connector\Api\AppConfigRepositoryInterface;
use Ortto\Connector\Api\Data\AppConfigInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;

class AppConfigRepository extends RestApiBase implements AppConfigRepositoryInterface
{
    private array $validKeys = [
        'instance_id' => Config::XML_PATH_INSTANCE_ID,
        'capture_js_url' => Config::XML_PATH_CAPTURE_JS_URL,
        'capture_api_url' => Config::XML_PATH_CAPTURE_API_URL,
        'magento_capture_js_url' => Config::XML_PATH_MAGENTO_CAPTURE_JS_URL,
        'tracking_code' => Config::XML_PATH_TRACKING_CODE,
        'enable' => Config::XML_PATH_ACTIVE,
        'api_key' => Config::XML_PATH_API_KEY,
        'enable_tracking' => Config::XML_PATH_TRACKING_ENABLED,
    ];

    private OrttoLoggerInterface $logger;
    private WriterInterface $configWriter;
    private StoreManagerInterface $storeManager;
    private \Ortto\Connector\Api\ConfigurationReaderInterface $configReader;
    private Pool $cacheFrontendPool;
    private \Magento\Framework\Encryption\EncryptorInterface $encryptor;

    /**
     * @param OrttoLoggerInterface $logger
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     * @param Pool $cacheFrontendPool
     * @param ScopeManagerInterface $scopeManager
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Ortto\Connector\Api\ConfigurationReaderInterface $reader
     */
    public function __construct(
        OrttoLoggerInterface $logger,
        WriterInterface $configWriter,
        StoreManagerInterface $storeManager,
        Pool $cacheFrontendPool,
        ScopeManagerInterface $scopeManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Ortto\Connector\Api\ConfigurationReaderInterface $reader
    ) {
        parent::__construct($scopeManager);
        $this->logger = $logger;
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->encryptor = $encryptor;
        $this->configReader = $reader;
    }

    /**
     * @throws Exception
     * @throws LocalizedException
     */
    public function update(AppConfigInterface $config)
    {
        $this->logger->debug("Configuration Request Received", $config->toArray());
        $this->validateScope($config->getScopeType(), $config->getScopeId(), false);

        $keys = $config->getKeys();
        if (empty($keys)) {
            throw $this->httpError('Requested keys cannot be empty', 400);
        }

        $toChange = [];
        foreach ($keys as $key => $value) {
            $configKey = $this->validKeys[$key];
            if (empty($configKey)) {
                throw $this->httpError(sprintf(
                    'Invalid configuration key %s. Acceptable keys are: %s',
                    $key,
                    implode(',', array_keys($this->validKeys))
                ), 400);
            }
            if ($key == 'enable' || $key == 'enable_tracking') {
                $value = To::bool($value) ? '1' : '0';
            }
            if ($key == 'api_key') {
                $value = $this->encryptor->encrypt($value);
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
                throw $this->httpError(sprintf('Invalid scope type %s', $scopeType), 400);
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

    /**
     * @inheriDoc
     */
    public function get(string $scopeType, int $scopeId)
    {
        try {
            $this->validateScope($scopeType, $scopeId, false);
            return [$this->configReader->getAllConfigs($scopeType, $scopeId)];
        } catch (\Exception $e) {
            return [["error" => $e->getMessage()]];
        }
    }

    /**
     * @inheriDoc
     */
    public function getAllStoreConfigs(): array
    {
        $result = [];
        try {
            $scopes = $this->scopeManager->getAllScopes();
            foreach ($scopes as $scope) {
                $scopeType = $scope->getType();
                $scopeId = $scope->getId();
                $result[] = [
                    'type' => $scopeType,
                    'id' => $scopeId,
                    'code' => $scope->getCode(),
                    'website' => [
                        'id' => $scope->getWebsiteId(),
                        'code' => $scope->getWebsiteCode(),
                    ],
                    'url' => $scope->getBaseURL(),
                    'connected' => $scope->isConnected(),
                    'config' => $this->configReader->getAllConfigs($scopeType, $scopeId),
                ];
            }
        } catch (\Exception $e) {
            return [["error" => $e->getMessage()]];
        }
        return $result;
    }
}
