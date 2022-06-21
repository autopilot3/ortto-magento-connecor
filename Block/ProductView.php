<?php
declare(strict_types=1);


namespace Ortto\Connector\Block;

use Ortto\Connector\Api\OrttoSerializerInterface;
use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Logger\OrttoLogger;
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
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;

class ProductView extends View
{
    private ProductDataFactory $productDataFactory;
    private TrackDataProviderInterface $trackDataProvider;
    private OrttoLogger $logger;
    private OrttoSerializerInterface $serializer;

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
        OrttoLogger $logger,
        OrttoSerializerInterface $serializer,
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
        $this->serializer = $serializer;
    }

    /**
     * @param string $event
     * @return string|bool
     */
    public function getEventJSON(string $event)
    {
        try {
            $product = $this->productDataFactory->create();
            $trackingData = $this->trackDataProvider->getData();
            $scope = $trackingData->getScope();
            if (!$product->load($this->getProduct(), $scope->getId())) {
                $this->logger->warn("Product View: Product not loaded");
                return false;
            }
            $payload = [
                'email' => $trackingData->getEmail(),
                'phone' => $trackingData->getPhone(),
                'payload' => [
                    'event' => $event,
                    'scope' => $scope->toArray(),
                    'data' => [
                        'product' => $product->toArray(),
                    ],
                ],
            ];

            return $this->serializer->serializeJson($payload);
        } catch (Exception $e) {
            $this->logger->error($e, "Product View: Failed to get product data");
            return false;
        }
    }
}
