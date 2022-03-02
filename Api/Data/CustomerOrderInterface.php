<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api\Data;

use Magento\Sales\Api\Data\OrderInterface;

interface CustomerOrderInterface
{
    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @return string
     */
    public function getCustomerEmail(): string;

    /**
     * @return OrderInterface[]
     */
    public function getOrders(): array;
}
