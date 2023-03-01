<?php
declare(strict_types=1);


namespace Ortto\Connector\Block;

use Ortto\Connector\Api\OrttoOrderRepositoryInterface;
use Ortto\Connector\Api\OrttoSerializerInterface;
use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Exception;

class CheckoutSuccess extends Template
{
    private TrackDataProviderInterface $trackDataProvider;
    private OrttoLogger $logger;
    private Session $session;
    private OrttoSerializerInterface $serializer;
    private OrttoOrderRepositoryInterface $orderRepository;

    public function __construct(
        Template\Context $context,
        TrackDataProviderInterface $trackDataProvider,
        OrttoLogger $logger,
        Session $session,
        OrttoSerializerInterface $serializer,
        OrttoOrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->trackDataProvider = $trackDataProvider;
        $this->logger = $logger;
        $this->session = $session;
        $this->serializer = $serializer;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return string|bool
     */
    public function getOrderEventJSON(string $event)
    {
        try {
            $trackingData = $this->trackDataProvider->getData();
            $scope = $trackingData->getScope();
            if ($sessionOrder = $this->session->getLastRealOrder()) {
                $orderId = $sessionOrder->getId();
                $order = $this->orderRepository->getById($scope, To::int($orderId));
                if (empty($order)) {
                    $this->logger->warn("Checkout Succeeded:Order Not Found",["id" => $orderId]);
                    return false;
                }
                $payload = [
                    'email' => $trackingData->getEmail(),
                    'phone' => $trackingData->getPhone(),
                    'payload' => [
                        'event' => $event,
                        'scope' => $scope->toArray(),
                        'data' => [
                            'order' => $order->serializeToArray(),
                        ],
                    ],
                ];
                return $this->serializer->serializeJson($payload);
            }
        } catch (Exception $e) {
            $this->logger->error($e, "Checkout Succeeded: Failed to get order data");
            return false;
        }
    }
}
