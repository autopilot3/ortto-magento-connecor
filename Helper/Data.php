<?php
declare(strict_types=1);

namespace Ortto\Connector\Helper;

use Magento\Catalog\Model\Product;
use Magento\ProductAlert\Model\Stock;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\SyncCategoryInterface;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Model\ResourceModel\CronCheckpoint\Collection as CheckpointCollection;
use Ortto\Connector\Model\ResourceModel\CronCheckpoint\CollectionFactory as CheckpointCollectionFactory;
use InvalidArgumentException;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Api\Data\OrderInterface;
use Ortto\Connector\Api\ConfigurationReaderInterface;
use Magento\Store\Model\ScopeInterface;
use DateTime;
use Exception;

class Data extends AbstractHelper
{
    public const SHIPPING_ADDRESS = "shipping_address";
    public const BILLING_ADDRESS = "billing_address";
    public const PHONE = "phone";
    private const ORDERS = "orders";

    private string $baseURL = "https://magento-integration-api.autopilotapp.com";
    private string $clientID = "mgqQkvCJWDFnxJTgQwfVuYEdQRWVAywE";

    private OrttoLoggerInterface $logger;
    private TimezoneInterface $timezone;
    private CustomerMetadataInterface $customerMetadata;
    private ConfigurationReaderInterface $config;
    private \DateTimeZone $utcTZ;
    private Product\Media\Config $productMedia;

    public function __construct(
        Context $context,
        TimezoneInterface $timezone,
        CustomerMetadataInterface $customerMetadata,
        OrttoLoggerInterface $logger,
        ConfigurationReaderInterface $config,
        \Magento\Catalog\Model\Product\Media\Config $productMedia
    ) {
        parent::__construct($context);
        $this->_request = $context->getRequest();
        $this->logger = $logger;
        $this->timezone = $timezone;
        $this->customerMetadata = $customerMetadata;
        $this->config = $config;

        $this->utcTZ = timezone_open('UTC');
        $this->productMedia = $productMedia;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getOrttoURL(string $path): string
    {
        $path = trim($path);
        $url = (string)$this->scopeConfig->getValue(Config::XML_PATH_BASE_URL);
        if (empty($url)) {
            $url = $this->baseURL;
        }
        if (empty($path)) {
            return rtrim($url, ' /');
        }
        return rtrim($url, ' /') . '/' . ltrim($path, '/');
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        $clientID = $this->scopeConfig->getValue(Config::XML_PATH_CLIENT_ID);
        if (empty($clientID)) {
            return $this->clientID;
        }
        return $clientID;
    }

    /**
     * @param mixed|null|string $gender
     * @return string
     */
    public function getGenderLabel($gender): string
    {
        if (empty($gender)) {
            return "";
        }
        try {
            $genderAttribute = $this->customerMetadata->getAttributeMetadata('gender');
            return (string)$genderAttribute->getOptions()[$gender]->getLabel();
        } catch (Exception $e) {
            $this->logger->error($e, 'Failed to fetch customer gender details');
            return "";
        }
    }

    /**
     * @param DateTime|string|null $value
     * @return string
     */
    public function toUTC($value): string
    {
        switch (true) {
            case is_string($value):
                if ($date = date_create($value, $this->utcTZ)) {
                    return $date->format(Config::DATE_TIME_FORMAT);
                }
                $this->logger->warn("Invalid date time", ['value' => $value]);
                return Config::EMPTY_DATE_TIME;
            case $value instanceof DateTime:
                $value->setTimezone($this->utcTZ);
                return $value->format(Config::DATE_TIME_FORMAT);
            default:
                return Config::EMPTY_DATE_TIME;
        }
    }

    public function formatDate($value): string
    {
        switch (true) {
            case is_string($value):
                if ($date = date_create($value)) {
                    return $date->format(Config::DATE_TIME_FORMAT);
                }
                $this->logger->warn("Invalid date time", ['value' => $value]);
                return Config::EMPTY_DATE_TIME;
            case $value instanceof DateTime:
                return $value->format(Config::DATE_TIME_FORMAT);
            default:
                return Config::EMPTY_DATE_TIME;
        }
    }

    /**
     * @return DateTime
     */
    public function nowInClientTimezone(): DateTime
    {
        return $this->timezone->date();
    }

    /**
     * @return DateTime
     */
    public function nowUTC(): DateTime
    {
        return date_create('now', $this->utcTZ);
    }

    public function shouldExportCustomer(ConfigScopeInterface $scope, CustomerInterface $customer): bool
    {
        if (!$this->config->isAutoSyncEnabled($scope->getType(), $scope->getId(), SyncCategoryInterface::CUSTOMER)) {
            $this->logger->debug(
                sprintf("Automatic %s synchronisation is off", SyncCategoryInterface::CUSTOMER),
                $scope->toArray()
            );
            return false;
        }
        if ($scope->getType() == ScopeInterface::SCOPE_WEBSITE) {
            return $customer->getWebsiteId() == $scope->getId();
        }
        return $customer->getStoreId() == $scope->getId() && $customer->getWebsiteId() == $scope->getWebsiteId();
    }

    public function shouldExportProduct(ConfigScopeInterface $scope, Product $product): bool
    {
        if (!$this->config->isAutoSyncEnabled($scope->getType(), $scope->getId(), SyncCategoryInterface::PRODUCT)) {
            $this->logger->debug(
                sprintf("Automatic %s synchronisation is off", SyncCategoryInterface::PRODUCT),
                $scope->toArray()
            );
            return false;
        }
        return array_contains($product->getWebsiteIds(), $scope->getWebsiteId(), false);
    }

    public function newHTTPException(string $message, int $code = 500): \Magento\Framework\Webapi\Exception
    {
        return new \Magento\Framework\Webapi\Exception(__($message), $code, $code);
    }

    /**
     * @param Product $product
     */
    public function getProductImageURL($product): string
    {
        $image = $product->getImage();
        if (empty($image) || $image == 'no_select') {
            return '';
        }

        return $this->productMedia->getMediaUrl($image);
        // Return cached image
        // $img = $this->imageFactory->create();
        // return $img->init($product, 'product_page_image_base')->setImageFile($image)->getUrl() ?? '';
    }
}
