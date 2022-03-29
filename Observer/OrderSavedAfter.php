<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Observer;

use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Helper\To;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Model\ResourceModel\OrderAttributes\CollectionFactory as OrderAttributeCollectionFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Event\Observer;
use Exception;
use Magento\Sales\Model\Order;

class OrderSavedAfter implements ObserverInterface
{
    private AutopilotLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;
    private AutopilotClientInterface $autopilotClient;
    private Data $helper;
    private OrderAttributeCollectionFactory $collectionFactory;

    public function __construct(
        AutopilotLoggerInterface $logger,
        ScopeManagerInterface $scopeManager,
        AutopilotClientInterface $autopilotClient,
        OrderAttributeCollectionFactory $collectionFactory,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->scopeManager = $scopeManager;
        $this->autopilotClient = $autopilotClient;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            /** @var OrderInterface $order */
            $order = $event->getData('order');
            if ($order->getState() == Order::STATE_CANCELED) {
                $attr = $this->setCancellationDate($order);
                if (!empty($attr)) {
                    $order->setExtensionAttributes($attr);
                }
            }
            $scopes = $this->scopeManager->getActiveScopes();
            foreach ($scopes as $scope) {
                if (!$this->helper->shouldExportOrder($scope, $order)) {
                    continue;
                }
                $this->autopilotClient->importOrders($scope, [$order]);
            }
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to export the order");
        }
    }

    /**
     * @param OrderInterface|Order $order
     * @return OrderExtensionInterface|null
     */
    private function setCancellationDate($order)
    {
        try {
            $collection = $this->collectionFactory->create();
            $nowUTC = $this->helper->nowUTC();
            $collection->setCancellationDate(To::int($order->getEntityId()), $nowUTC);
            $attr = $order->getExtensionAttributes();
            $attr->setAutopilotCanceledAt($this->helper->toUTC($nowUTC));
        } catch (Exception $e) {
            $msg = sprintf(
                'Failed to update order cancellation attribute (ID: %d)',
                To::int($order->getEntityId())
            );
            $this->logger->error($e, $msg);
            return null;
        }

        return $attr;
    }
}
