<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Block\Catalog\Product;

use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\Data\TrackingDataInterface;
use Autopilot\AP3Connector\Api\TrackDataProviderInterface;
use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Logger\Logger;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Autopilot\AP3Connector\Model\Api\ProductDataFactory;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Serialize\JsonConverter;
use Exception;

class View extends \Magento\Catalog\Block\Product\View
{
    private ProductDataFactory $productDataFactory;
    private TrackDataProviderInterface $trackDataProvider;
    private Logger $logger;

    public function __construct(
        Context $context,
        EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        ProductDataFactory $productDataFactory,
        TrackDataProviderInterface $trackDataProvider,
        Logger $logger,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
        $this->productDataFactory = $productDataFactory;
        $this->trackDataProvider = $trackDataProvider;
        $this->logger = $logger;
    }

    /**
     * @param string $event
     * @return array|bool
     */
    public function getProductEvent(string $event)
    {
        try {
            $factory = $this->productDataFactory->create();
            $factory->load($this->getProduct());
            $product = $factory->toArray();
            if (empty($product)) {
                return false;
            }
            $trackingData = $this->trackDataProvider->getData();
            $payload = [
                'resource' => Config::RESOURCE_PRODUCT,
                'event' => $event,
                TrackingDataInterface::SCOPE => [
                    ConfigScopeInterface::ID => $trackingData->getScopeId(),
                    ConfigScopeInterface::TYPE => $trackingData->getScopeType(),
                ],
                'data' => [
                    'product' => $product,
                ],
            ];
            return [
                TrackingDataInterface::EMAIL => $trackingData->getEmail(),
                TrackingDataInterface::PHONE => $trackingData->getPhone(),
                TrackingDataInterface::PAYLOAD => JsonConverter::convert($payload),
            ];
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to get product data");
            return false;
        }
    }
}
