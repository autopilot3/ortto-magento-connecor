<?php
declare(strict_types=1);


namespace Ortto\Connector\Block;

use Ortto\Connector\Api\Data\TrackingDataInterface as TD;
use Ortto\Connector\Api\OrttoSerializerInterface;
use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Logger\OrttoLogger;
use Ortto\Connector\Model\Api\OrderDataFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Exception;

class CheckoutSuccess extends Template
{
    private TrackDataProviderInterface $trackDataProvider;
    private OrttoLogger $logger;
    private Session $session;
    private OrderDataFactory $orderDataFactory;
    private OrttoSerializerInterface $serializer;

    public function __construct(
        Template\Context $context,
        TrackDataProviderInterface $trackDataProvider,
        OrderDataFactory $cartDataFactory,
        OrttoLogger $logger,
        Session $session,
        OrttoSerializerInterface $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->trackDataProvider = $trackDataProvider;
        $this->orderDataFactory = $cartDataFactory;
        $this->logger = $logger;
        $this->session = $session;
        $this->serializer = $serializer;
    }

    /**
     * @return string|bool
     */
    public function getOrderEventJSON(string $event)
    {
        try {
            $order = $this->orderDataFactory->create();
            if (!$order->load($this->session->getLastRealOrder())) {
                $this->logger->warn("Checkout Succeeded: Order not loaded");
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
                        'order' => $order->toArray(),
                    ],
                ],
            ];
            return $this->serializer->serializeJson($payload);
        } catch (Exception $e) {
            $this->logger->error($e, "Checkout Succeeded: Failed to get order data");
            return false;
        }
    }
}
