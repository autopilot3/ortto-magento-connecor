<?php

namespace Ortto\Connector\Controller\Cart;

use Magento\Checkout\Model\Session;
use Ortto\Connector\Api\RoutesInterface;
use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Controller\AbstractJsonController;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Model\Api\CartDataFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\Action\Context;

class Get extends AbstractJsonController implements HttpGetActionInterface
{
    private CartDataFactory $cartDataFactory;
    private TrackDataProviderInterface $trackDataProvider;
    private OrttoLoggerInterface $logger;
    private Session $session;

    public function __construct(
        Context $context,
        OrttoLoggerInterface $logger,
        CartDataFactory $cartDataFactory,
        TrackDataProviderInterface $trackDataProvider,
        Session $session
    ) {
        parent::__construct($context, $logger);
        $this->cartDataFactory = $cartDataFactory;
        $this->trackDataProvider = $trackDataProvider;
        $this->logger = $logger;
        $this->session = $session;
    }

    /**
     * @return Json
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $this->logger->debug("Request received: " . $this->getUrl(RoutesInterface::MG_CART_GET), $params);
            $sku = (string)$params['sku'];
            if (empty($sku)) {
                return $this->error("Product SKU was not specified");
            }

            $trackingData = $this->trackDataProvider->getData();
            $cartData = $this->cartDataFactory->create();
            if ($cartData->load($this->session->getQuote())) {
                $payload = [
                    'event' => Config::EVENT_TYPE_PRODUCT_ADDED_TO_CART,
                    'scope' => $trackingData->getScope()->toArray(),
                    'data' => [
                        'cart' => $cartData->toArray(),
                        'sku' => $sku,
                    ],
                ];
                return $this->success($payload);
            }
            return $this->error("Get: The shopping cart was empty");
        } catch (\Exception $e) {
            return $this->error("Failed to load shopping cart data", $e);
        }
    }
}
