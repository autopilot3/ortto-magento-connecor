<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Plugin;

use Autopilot\AP3Connector\Helper\To;
use Autopilot\AP3Connector\Model\ResourceModel\OrderAttributes\CollectionFactory as OrderAttributeCollectionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;

class OrderRepositoryPlugin
{
    private OrderAttributeCollectionFactory $attrCollectionFactory;

    public function __construct(
        OrderAttributeCollectionFactory $attrCollectionFactory
    ) {
        $this->attrCollectionFactory = $attrCollectionFactory;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order): OrderInterface
    {
        $extAttributes = $order->getExtensionAttributes();
        if ($extAttributes && $extAttributes->getOrttoCanceledAt()) {
            return $order;
        }

        $attributesCollection = $this->attrCollectionFactory->create();
        $attribute = $attributesCollection->getByOrderId(To::int($order->getId()));
        if (!empty($attribute)) {
            $extAttributes->setOrttoCanceledAt($attribute->getCanceledAt());
            $extAttributes->setOrttoCompletedAt($attribute->getCompletedAt());
            $order->setExtensionAttributes($extAttributes);
        }
        return $order;
    }

    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchResults
    ): OrderSearchResultInterface {
        foreach ($searchResults->getItems() as $item) {
            $this->afterGet($subject, $item);
        }
        return $searchResults;
    }
}
