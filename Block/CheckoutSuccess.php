<?php
declare(strict_types=1);


namespace Ortto\Connector\Block;

use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\Data\TrackingDataInterface as TD;
use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Logger\Logger;
use Ortto\Connector\Model\Api\OrderDataFactory;
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
