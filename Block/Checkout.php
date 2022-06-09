<?php
declare(strict_types=1);


namespace Ortto\Connector\Block;

use Ortto\Connector\Api\Data\TrackingDataInterface as TD;
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
     * @return array|bool
     */
    public function getCardEvent(string $event)
    {
        try {
            $factory = $this->cartDataFactory->create();
            $factory->load($this->session->getQuote());
            $cart = $factory->toArray();
            if (empty($cart)) {
                return false;
            }

            $trackingData = $this->trackDataProvider->getData();

            $payload = [
                'event' => $event,
                'scope' => $trackingData->getScope()->toArray(),
                'data' => [
                    'cart' => $cart,
                ],
            ];
            return [
                TD::EMAIL => $trackingData->getEmail(),
                TD::PHONE => $trackingData->getPhone(),
                TD::PAYLOAD => $this->serializer->serializeJson($payload),
            ];
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to get cart data");
            return false;
        }
    }
}
