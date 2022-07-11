<?php
declare(strict_types=1);


namespace Ortto\Connector\Block;

use Ortto\Connector\Api\OrttoCartRepositoryInterface;
use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Ortto\Connector\Service\OrttoSerializer;
use Exception;

class Checkout extends Template
{
    private TrackDataProviderInterface $trackDataProvider;
    private OrttoLogger $logger;
    private Session $session;
    private OrttoSerializer $serializer;
    private OrttoCartRepositoryInterface $cartRepository;

    public function __construct(
        Template\Context $context,
        TrackDataProviderInterface $trackDataProvider,
        OrttoCartRepositoryInterface $cartRepository,
        OrttoLogger $logger,
        Session $session,
        OrttoSerializer $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->trackDataProvider = $trackDataProvider;
        $this->logger = $logger;
        $this->session = $session;
        $this->serializer = $serializer;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param string $event
     * @return string|bool
     */
    public function getCardEventJSON(string $event)
    {
        try {
            $trackingData = $this->trackDataProvider->getData();
            $scope = $trackingData->getScope();
            $cart = $this->cartRepository->getById($scope, To::int($this->session->getQuoteId()));
            $payload = [
                'email' => $trackingData->getEmail(),
                'phone' => $trackingData->getPhone(),
                'payload' => [
                    'event' => $event,
                    'scope' => $scope->toArray(),
                    'data' => [
                        'cart' => $cart->serializeToArray(),
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
