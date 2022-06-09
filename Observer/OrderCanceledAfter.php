<?php
declare(strict_types=1);

namespace Ortto\Connector\Observer;

use Magento\Store\Model\ScopeInterface;
use Ortto\Connector\Api\OrttoClientInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Magento\Framework\Event\Observer;
use Ortto\Connector\Model\ResourceModel\OrderAttributes\CollectionFactory as OrderAttributeCollectionFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Exception;

class OrderCanceledAfter implements ObserverInterface
{
    private OrttoLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;
    private OrttoClientInterface $orttoClient;
    private Data $helper;
    private OrderAttributeCollectionFactory $collectionFactory;

    public function __construct(
        OrttoLoggerInterface $logger,
        OrderAttributeCollectionFactory $collectionFactory,
        OrttoClientInterface $orttoClient,
        ScopeManagerInterface $scopeManager,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->orttoClient = $orttoClient;
        $this->scopeManager = $scopeManager;
    }

    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var OrderInterface $order */
        $order = $event->getData('order');
        $now = $this->helper->nowUTC();
        $orderId = To::int($order->getEntityId());
        $storeId = To::int($order->getStoreId());
        try {
            $collection = $this->collectionFactory->create();
            $collection->setCancellationDate($orderId, $now);
        } catch (Exception $e) {
            $msg = sprintf('Failed to update order cancellation attribute (ID: %d, Store: %d)', $orderId, $storeId);
            $this->logger->error($e, $msg);
        }

        $attr = $order->getExtensionAttributes();
        $attr->setOrttoCanceledAt($this->helper->toUTC($now));
        $order->setExtensionAttributes($attr);

        try {
            $scope = $this->scopeManager->initialiseScope(ScopeInterface::SCOPE_STORE, $storeId);
            $this->orttoClient->importOrders($scope, [$order]);
        } catch (\Exception $e) {
            $msg = sprintf('Failed to export the cancelled order ID %d for store ID %d', $orderId, $storeId);
            $this->logger->error($e, $msg);
        }
    }
}
