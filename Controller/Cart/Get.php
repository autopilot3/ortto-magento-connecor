<?php

namespace Autopilot\AP3Connector\Controller\Cart;

use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\RoutesInterface;
use Autopilot\AP3Connector\Api\TrackDataProviderInterface;
use Autopilot\AP3Connector\Controller\AbstractJsonController;
use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Helper\To;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\Api\CartItemDataFactory;
use Autopilot\AP3Connector\Model\Api\CartDataFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action\Context;

class Get extends AbstractJsonController implements HttpGetActionInterface
{
    private CartDataFactory $cartDataFactory;
    private Session $session;
    private TrackDataProviderInterface $trackDataProvider;
    private AutopilotLoggerInterface $logger;

    public function __construct(
        Context $context,
        AutopilotLoggerInterface $logger,
        CartItemDataFactory $cartItemDataFactory,
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
            $trackingData = $this->trackDataProvider->getData();
            $quote = $this->session->getQuote();
            $newProductIds = [];
            foreach ($quote->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                $productId = $product->getEntityId();
                if (empty($product) || !in_array($productId, $productIds)) {
                    continue;
                }
                $newProductIds[] = To::int($productId);
            }

            if (empty($newProductIds)) {
                return $this->error("At least one product must be added to the cart");
            }

            $cartData = $this->cartDataFactory->create();
            $cartData->load($quote);

            $payload = [
                'resource' => Config::RESOURCE_PRODUCT,
                'event' => Config::EVENT_TYPE_ADDED_TO_CARD,
                'scope' => [
                    ConfigScopeInterface::ID => $trackingData->getScopeId(),
                    ConfigScopeInterface::TYPE => $trackingData->getScopeType(),
                ],
                'data' => [
                    'cart' => $cartData->toArray(),
                    'new_product_ids' => $newProductIds,
                    'sku' => $sku,
                ],
            ];
            return $this->success($payload);
        } catch (\Exception $e) {
            return $this->error("Failed to load shopping cart data", $e);
        }
    }
}
