<?php
declare(strict_types=1);

namespace Ortto\Connector\Observer;

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
    private Data $helper;
    private OrderAttributeCollectionFactory $collectionFactory;

    public function __construct(
        OrttoLoggerInterface $logger,
        OrderAttributeCollectionFactory $collectionFactory,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
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
    }
}
