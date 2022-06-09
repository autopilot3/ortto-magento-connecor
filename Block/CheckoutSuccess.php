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
     * @return array|bool
     */
    public function getOrderEvent()
    {
        try {
            $order = $this->orderDataFactory->create();
            $order->load($this->session->getLastRealOrder());
            $orderData = $order->toArray();
            if (empty($orderData)) {
                return false;
            }

            $trackingData = $this->trackDataProvider->getData();

            $payload = [
                'event' => Config::EVENT_TYPE_ORDER_CREATED,
                'scope' => $trackingData->getScope()->toArray(),
                'data' => [
                    'order' => $orderData,
                ],
            ];
            return [
                TD::EMAIL => $trackingData->getEmail(),
                TD::PHONE => $trackingData->getPhone(),
                TD::PAYLOAD => $this->serializer->serializeJson($payload),
            ];
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to get order data");
            return false;
        }
    }
}
