<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Plugin;

use AutoPilot\AP3Connector\Model\ResourceModel\OrderAttributes\CollectionFactory as OrderAttributeCollectionFactory;
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
        if ($extAttributes && $extAttributes->getAutopilotCanceledAt()) {
            return $order;
        }

        $attributesCollection = $this->attrCollectionFactory->create();
        $attribute = $attributesCollection->getByOrderId((int)$order->getId());
        if (!empty($attribute)) {
            $extAttributes->setAutopilotCanceledAt($attribute->getCanceledAt());
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
