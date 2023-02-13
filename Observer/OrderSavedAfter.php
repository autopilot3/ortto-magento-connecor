<?php
declare(strict_types=1);

namespace Ortto\Connector\Observer;

use Ortto\Connector\Helper\Data;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Model\ResourceModel\OrderAttributes\CollectionFactory as OrderAttributeCollectionFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Event\Observer;
use Exception;
use Magento\Sales\Model\Order;

class OrderSavedAfter implements ObserverInterface
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
        /*
         * NOTE: Admin observer. Order creation is handled by capture.
         */
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
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to export the order");
        }
    }
}
