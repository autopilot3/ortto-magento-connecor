<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api\Data;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Sales\Api\Data\OrderInterface;

interface CustomerDataInterface
{
    /**
     * @return CustomerInterface
     */
    public function getCustomer(): CustomerInterface;

    /**
     * @return OrderInterface[]
     */
    public function getOrders(): array;
}
