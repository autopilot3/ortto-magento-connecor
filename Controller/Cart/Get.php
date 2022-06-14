<?php

namespace Ortto\Connector\Controller\Cart;

use Ortto\Connector\Api\RoutesInterface;
use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Controller\AbstractJsonController;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Model\Api\CartDataFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action\Context;

class Get extends AbstractJsonController implements HttpGetActionInterface
{
    private CartDataFactory $cartDataFactory;
    private Session $session;
    private TrackDataProviderInterface $trackDataProvider;
    private OrttoLoggerInterface $logger;

    public function __construct(
        Context $context,
        OrttoLoggerInterface $logger,
        CartDataFactory $cartDataFactory,
        Session $session,
        TrackDataProviderInterface $trackDataProvider
    ) {
        parent::__construct($context, $logger);
        $this->cartDataFactory = $cartDataFactory;
        $this->session = $session;
        $this->trackDataProvider = $trackDataProvider;
        $this->logger = $logger;
    }

    /**
     * @return Json
     */
    public function execute()
    {
        try {
            $this->logger->info(
                "SESSION",
                ['exists' => $this->session->isSessionExists(), 'id' => $this->session->getSessionId()]
            );
            $params = $this->getRequest()->getParams();
            $this->logger->debug("Request received: " . $this->getUrl(RoutesInterface::MG_CART_GET), $params);
            $sku = (string)$params['sku'];
            if (empty($sku)) {
                return $this->error("Product SKU was not specified");
            }
            $productIds = $params['product_ids'];
            if (empty($productIds)) {
                return $this->error("No product was added to the cart");
            }

            $newProductIds = [];
            foreach ($productIds as $productId) {
                $newProductIds[] = To::int($productId);
            }

            $cartData = $this->cartDataFactory->create();
            $quote = $this->session->getQuote();
            $this->logger->info("QUOTE", ['id' => $quote->getEntityId()]);
            $this->logger->info("QUOTE ID", ['id' => $this->session->getQuoteId()]);
            if ($cartData->load($quote)) {
                $trackingData = $this->trackDataProvider->getData();
                $payload = [
                    'event' => Config::EVENT_TYPE_PRODUCT_ADDED_TO_CART,
                    'scope' => $trackingData->getScope()->toArray(),
                    'data' => [
                        'cart' => $cartData->toArray(),
                        'new_product_ids' => $newProductIds,
                        'sku' => $sku,
                    ],
                ];
                return $this->success($payload);
            }
            return $this->error("The cart is empty");
        } catch (\Exception $e) {
            return $this->error("Failed to load shopping cart data", $e);
        }
    }
}
