<?php
declare(strict_types=1);


namespace Ortto\Connector\Block;

use Ortto\Connector\Api\OrttoSerializerInterface;
use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Ortto\Connector\Model\Api\OrttoProductRepository;
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
    private TrackDataProviderInterface $trackDataProvider;
    private OrttoLogger $logger;
    private OrttoSerializerInterface $serializer;
    private OrttoProductRepository $orttoProductRepository;

    public function __construct(
        Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        TrackDataProviderInterface $trackDataProvider,
        OrttoLogger $logger,
        OrttoSerializerInterface $serializer,
        OrttoProductRepository $orttoProductRepository,
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
        $this->trackDataProvider = $trackDataProvider;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->orttoProductRepository = $orttoProductRepository;
    }

    /**
     * @param string $event
     * @return string|bool
     */
    public function getEventJSON(string $event)
    {
        try {
            $trackingData = $this->trackDataProvider->getData();
            if (!$trackingData->isTrackingEnabled()) {
                return false;
            }
            $scope = $trackingData->getScope();
            if ($p = $this->getProduct()) {
                $productId = To::int($p->getId());
            } else {
                $this->logger->warn("Product View: Product not loaded");
                return false;
            }
            $product = $this->orttoProductRepository->getById($scope, $productId);
            if (empty($product)) {
                $this->logger->warn("Product View: Product Not Found", ["id" => $productId]);
                return false;
            }
            $payload = [
                'email' => $trackingData->getEmail(),
                'phone' => $trackingData->getPhone(),
                'payload' => [
                    'event' => $event,
                    'scope' => $scope->toArray(),
                    'data' => [
                        'product' => $product->serializeToArray(),
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
