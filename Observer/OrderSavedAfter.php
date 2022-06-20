<?php
declare(strict_types=1);

namespace Ortto\Connector\Observer;

use Magento\Store\Model\ScopeInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Api\OrttoClientInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Model\ResourceModel\OrderAttributes\CollectionFactory as OrderAttributeCollectionFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Event\Observer;
use Exception;
use Magento\Sales\Model\Order;

class OrderSavedAfter implements ObserverInterface
{
    private OrttoLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;
    private OrttoClientInterface $orttoClient;
    private Data $helper;
    private OrderAttributeCollectionFactory $collectionFactory;

    public function __construct(
        OrttoLoggerInterface $logger,
        ScopeManagerInterface $scopeManager,
        OrttoClientInterface $orttoClient,
        OrderAttributeCollectionFactory $collectionFactory,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->scopeManager = $scopeManager;
        $this->orttoClient = $orttoClient;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            /** @var OrderInterface $order */
            $order = $event->getData('order');
            switch ($order->getState()) {
                case Order::STATE_CANCELED:
                    // Cancelled orders are processed by the Cancellation observer
                    return;
                case Order::STATE_COMPLETE:
                    try {
                        $now = $this->helper->nowUTC();
                        $collection = $this->collectionFactory->create();
                        $collection->setCompletionDate((int)$order->getEntityId(), $now);
                        $attr = $order->getExtensionAttributes();
                        $attr->setOrttoCompletedAt($this->helper->toUTC($now));
                        $order->setExtensionAttributes($attr);
                    } catch (Exception $e) {
                        $msg = sprintf(
                            'Failed to update order completion date (ID: %d)',
                            (int)$order->getEntityId()
                        );
                        $this->logger->error($e, $msg);
                    }
            }
            $this->syncOrder($order);
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to export the order");
        }
    }

    private function syncOrder(OrderInterface $order)
    {
        try {
            $scope = $this->scopeManager->initialiseScope(
                ScopeInterface::SCOPE_STORE,
                To::int($order->getStoreId())
            );

            $this->orttoClient->importOrder($scope, $order);
        } catch (\Exception $e) {
            $this->logger->error($e, "Failed to export the closed order");
        }
    }
}
