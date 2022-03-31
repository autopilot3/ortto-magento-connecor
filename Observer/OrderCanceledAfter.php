<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Observer;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Magento\Framework\Event\Observer;
use Autopilot\AP3Connector\Model\ResourceModel\OrderAttributes\CollectionFactory as OrderAttributeCollectionFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Exception;

class OrderCanceledAfter implements ObserverInterface
{
    private AutopilotLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;
    private AutopilotClientInterface $autopilotClient;
    private Data $helper;
    private OrderAttributeCollectionFactory $collectionFactory;

    public function __construct(
        AutopilotLoggerInterface $logger,
        OrderAttributeCollectionFactory $collectionFactory,
        AutopilotClientInterface $autopilotClient,
        ScopeManagerInterface $scopeManager,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->autopilotClient = $autopilotClient;
        $this->scopeManager = $scopeManager;
    }

    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var OrderInterface $order */
        $order = $event->getData('order');
        $now = $this->helper->nowUTC();
        try {
            $collection = $this->collectionFactory->create();
            $collection->setCancellationDate((int)$order->getEntityId(), $now);
        } catch (Exception $e) {
            $msg = sprintf(
                'Failed to update order cancellation attribute (ID: %d)',
                (int)$order->getEntityId()
            );
            $this->logger->error($e, $msg);
        }

        $attr = $order->getExtensionAttributes();
        $attr->setAutopilotCanceledAt($this->helper->toUTC($now));
        $order->setExtensionAttributes($attr);

        $scopes = $this->scopeManager->getActiveScopes();
        foreach ($scopes as $scope) {
            if (array_contains($scope->getStoreIds(), (int)$order->getStoreId())) {
                try {
                    $this->autopilotClient->importOrders($scope, [$order]);
                } catch (Exception $e) {
                    $msg = sprintf(
                        'Failed to export the cancelled order ID %d to %s',
                        (int)$order->getEntityId(),
                        $scope->getCode()
                    );
                    $this->logger->error($e, $msg);
                }
            }
        }
    }
}
