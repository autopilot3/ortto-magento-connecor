<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Observer;

use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use AutoPilot\AP3Connector\Model\ResourceModel\OrderAttributes\CollectionFactory as OrderAttributeCollectionFactory;

class OrderAfterCancel implements ObserverInterface
{
    private AutopilotLoggerInterface $logger;
    private Data $helper;
    private OrderAttributeCollectionFactory $collectionFactory;

    public function __construct(
        AutopilotLoggerInterface $logger,
        OrderAttributeCollectionFactory $collectionFactory,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            /** @var OrderInterface $order */
            $order = $event->getData('order');
            $collection = $this->collectionFactory->create();
            $collection->setCancellationDate((int)$order->getEntityId(), $this->helper->now());
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to update order attributes");
        }
    }
}
