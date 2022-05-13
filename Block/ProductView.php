<?php
declare(strict_types=1);


namespace Ortto\Connector\Block;

use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\Data\TrackingDataInterface as TD;
use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Logger\Logger;
use Ortto\Connector\Model\Api\ProductDataFactory;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\JsonConverter;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;

class ProductView extends View
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
                'event' => $event,
                'scope' => [
                    ConfigScopeInterface::ID => $trackingData->getScopeId(),
                    ConfigScopeInterface::TYPE => $trackingData->getScopeType(),
                ],
                'data' => [
                    'product' => $product,
                ],
            ];
            return [
                TD::EMAIL => $trackingData->getEmail(),
                TD::PHONE => $trackingData->getPhone(),
                TD::PAYLOAD => JsonConverter::convert($payload),
            ];
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to get product data");
            return false;
        }
    }
}
