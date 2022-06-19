<?php
declare(strict_types=1);


namespace Ortto\Connector\Block;

use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Logger\OrttoLogger;
use Ortto\Connector\Model\Api\CartDataFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Ortto\Connector\Service\OrttoSerializer;
use Exception;

class Checkout extends Template
{
    private TrackDataProviderInterface $trackDataProvider;
    private OrttoLogger $logger;
    private Session $session;
    private CartDataFactory $cartDataFactory;
    private OrttoSerializer $serializer;

    public function __construct(
        Template\Context $context,
        TrackDataProviderInterface $trackDataProvider,
        CartDataFactory $cartDataFactory,
        OrttoLogger $logger,
        Session $session,
        OrttoSerializer $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->trackDataProvider = $trackDataProvider;
        $this->cartDataFactory = $cartDataFactory;
        $this->logger = $logger;
        $this->session = $session;
        $this->serializer = $serializer;
    }

    /**
     * @param string $event
     * @return string|bool
     */
    public function getCardEventJSON(string $event)
    {
        try {
            $cart = $this->cartDataFactory->create();
            if (!$cart->load($this->session->getQuote())) {
                $this->logger->warn("Checkout Started: Quote not loaded");
                return false;
            }

            $trackingData = $this->trackDataProvider->getData();

            $payload = [
                'email' => $trackingData->getEmail(),
                'phone' => $trackingData->getPhone(),
                'payload' => [
                    'event' => $event,
                    'scope' => $trackingData->getScope()->toArray(),
                    'data' => [
                        'cart' => $cart->toArray(),
                    ],
                ],
            ];
            return $this->serializer->serializeJson($payload);
        } catch (Exception $e) {
            $this->logger->error($e, "Checkout Started: Failed to get cart data");
            return false;
        }
    }
}
