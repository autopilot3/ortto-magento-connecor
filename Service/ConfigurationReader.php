<?php
declare(strict_types=1);


namespace Ortto\Connector\Service;

use Ortto\Connector\Api\ConfigurationReaderInterface;
use Ortto\Connector\Api\ImageIdInterface;
use Ortto\Connector\Api\SyncCategoryInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Helper\To;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class ConfigurationReader implements ConfigurationReaderInterface
{
    private EncryptorInterface $encryptor;
    private ScopeConfigInterface $scopeConfig;

    private string $apiKey;

    public function __construct(EncryptorInterface $encryptor, ScopeConfigInterface $scopeConfig)
    {
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->apiKey = '';
    }

    /**
     * @inheirtDoc
     */
    public function isActive(string $scopeType, int $scopeId): bool
    {
        return To::bool($this->scopeConfig->getValue(Config::XML_PATH_ACTIVE, $scopeType, $scopeId));
    }

    /**
     * @inheirtDoc
     */
    public function isTrackingEnabled(string $scopeType, int $scopeId): bool
    {
        return To::bool($this->scopeConfig->getValue(Config::XML_PATH_TRACKING_ENABLED, $scopeType, $scopeId));
    }

    /**
     * @inheirtDoc
     */
    public function getAPIKey(string $scopeType, int $scopeId): string
    {
        if ($this->apiKey !== '') {
            return $this->apiKey;
        }
        $encrypted = trim((string)$this->scopeConfig->getValue(Config::XML_PATH_API_KEY, $scopeType, $scopeId));
        if (empty($encrypted)) {
            return "";
        }
        $this->apiKey = $this->encryptor->decrypt($encrypted);
        return $this->apiKey;
    }

    /**
     * @inheirtDoc
     */
    public function isAutoSyncEnabled(string $scopeType, int $scopeId, string $category): bool
    {
        switch ($category) {
            case SyncCategoryInterface::CUSTOMER:
                return To::bool($this->scopeConfig->getValue(
                    Config::XML_PATH_SYNC_CUSTOMER_AUTO_ENABLED,
                    $scopeType,
                    $scopeId
                ));
            case SyncCategoryInterface::ORDER:
                return To::bool($this->scopeConfig->getValue(
                    Config::XML_PATH_SYNC_ORDER_AUTO_ENABLED,
                    $scopeType,
                    $scopeId
                ));
            case SyncCategoryInterface::PRODUCT:
                return To::bool($this->scopeConfig->getValue(
                    Config::XML_PATH_SYNC_PRODUCT_AUTO_ENABLED,
                    $scopeType,
                    $scopeId
                ));
            default:
                return false;
        }
    }

    /**
     * @inheirtDoc
     */
    public function isAnonymousOrderSyncEnabled(string $scopeType, int $scopeId): bool
    {
        return To::bool($this->scopeConfig->getValue(
            Config::XML_PATH_SYNC_ANONYMOUS_ORDERS_ENABLED,
            $scopeType,
            $scopeId
        ));
    }

    /**
     * @inheirtDoc
     */
    public function getPlaceholderImages(string $scopeType, int $scopeId): array
    {
        return [
            ImageIdInterface::IMAGE => $this->scopeConfig->getValue(
                Config::XML_PATH_IMAGE_PLACE_HOLDER,
                $scopeType,
                $scopeId
            ),
            ImageIdInterface::SMALL => $this->scopeConfig->getValue(
                Config::XML_PATH_SMALL_IMAGE_PLACE_HOLDER,
                $scopeType,
                $scopeId
            ),
            ImageIdInterface::SWATCH => $this->scopeConfig->getValue(
                Config::XML_PATH_SWATCH_IMAGE_PLACE_HOLDER,
                $scopeType,
                $scopeId
            ),
            ImageIdInterface::THUMBNAIL => $this->scopeConfig->getValue(
                Config::XML_PATH_THUMBNAIL_IMAGE_PLACE_HOLDER,
                $scopeType,
                $scopeId
            ),
        ];
    }

    public function getTrackingCode(string $scopeType, int $scopeId): string
    {
        return (string)$this->scopeConfig->getValue(
            Config::XML_PATH_TRACKING_CODE,
            $scopeType,
            $scopeId
        );
    }

    public function getCaptureJsURL(string $scopeType, int $scopeId): string
    {
        return (string)$this->scopeConfig->getValue(
            Config::XML_PATH_CAPTURE_JS_URL,
            $scopeType,
            $scopeId
        );
    }

    public function getMagentoCaptureJsURL(string $scopeType, int $scopeId): string
    {
        return (string)$this->scopeConfig->getValue(
            Config::XML_PATH_MAGENTO_CAPTURE_JS_URL,
            $scopeType,
            $scopeId
        );
    }

    public function getCaptureApiURL(string $scopeType, int $scopeId): string
    {
        return (string)$this->scopeConfig->getValue(
            Config::XML_PATH_CAPTURE_API_URL,
            $scopeType,
            $scopeId
        );
    }

    public function getInstanceId(string $scopeType, int $scopeId): string
    {
        return (string)$this->scopeConfig->getValue(
            Config::XML_PATH_INSTANCE_ID,
            $scopeType,
            $scopeId
        );
    }
}
