<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Observer;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Helper\To;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use AutoPilot\AP3Connector\Model\ResourceModel\OrderAttributes\CollectionFactory as OrderAttributeCollectionFactory;

class OrderCanceledAfter implements ObserverInterface
{
    private AutopilotLoggerInterface $logger;
    private Data $helper;
    private OrderAttributeCollectionFactory $collectionFactory;
    private AutopilotClientInterface $autopilotClient;
    private ScopeManagerInterface $scopeManager;

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
        $now = $this->helper->now();
        try {
            $collection = $this->collectionFactory->create();
            $collection->setCancellationDate(To::int($order->getEntityId()), $now);
        } catch (Exception $e) {
            $msg = sprintf(
                'Failed to update order cancellation attribute (ID: %d)',
                To::int($order->getEntityId())
            );
            $this->logger->error($e, $msg);
        }

        $attr = $order->getExtensionAttributes();
        $attr->setAutopilotCanceledAt($this->helper->formatDateTime($now));
        $order->setExtensionAttributes($attr);

        $scopes = $this->scopeManager->getActiveScopes();
        foreach ($scopes as $scope) {
            if (!$this->helper->shouldExportOrder($scope, $order)) {
                continue;
            }
            try {
                $this->autopilotClient->importOrders($scope, [$order]);
            } catch (Exception $e) {
                $msg = sprintf(
                    'Failed to export the cancelled order ID %d to %s',
                    To::int($order->getEntityId()),
                    $scope->getCode()
                );
                $this->logger->error($e, $msg);
            }
        }
    }
}
