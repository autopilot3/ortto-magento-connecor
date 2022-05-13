<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Block;

use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\Data\TrackingDataInterface as TD;
use Autopilot\AP3Connector\Api\TrackDataProviderInterface;
use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Logger\Logger;
use Autopilot\AP3Connector\Model\Api\OrderDataFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\JsonConverter;
use Magento\Framework\View\Element\Template;
use Exception;

class CheckoutSuccess extends Template
{
    private TrackDataProviderInterface $trackDataProvider;
    private Logger $logger;
    private Session $session;
    private OrderDataFactory $orderDataFactory;

    public function __construct(
        Template\Context $context,
        TrackDataProviderInterface $trackDataProvider,
        OrderDataFactory $cartDataFactory,
        Logger $logger,
        Session $session,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->trackDataProvider = $trackDataProvider;
        $this->orderDataFactory = $cartDataFactory;
        $this->logger = $logger;
        $this->session = $session;
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
                'scope' => [
                    ConfigScopeInterface::ID => $trackingData->getScopeId(),
                    ConfigScopeInterface::TYPE => $trackingData->getScopeType(),
                ],
                'data' => [
                    'order' => $orderData,
                ],
            ];
            return [
                TD::EMAIL => $trackingData->getEmail(),
                TD::PHONE => $trackingData->getPhone(),
                TD::PAYLOAD => JsonConverter::convert($payload),
            ];
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to get order data");
            return false;
        }
    }
}
