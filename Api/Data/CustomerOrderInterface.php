<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api\Data;

use Magento\Sales\Api\Data\OrderInterface;

interface CustomerOrderInterface
{
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_EMAIL = 'customer_email';
    const CUSTOMER_ORDERS = 'orders';

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId);

    /**
     * @return string
     */
    public function getCustomerEmail(): string;

    /**
     * @param string $email
     * @return $this
     */
    public function setCustomerEmail(string $email);

    /**
     * @return OrderInterface[]
     */
    public function getOrders(): array;

    /**
     * @param OrderInterface[] $orders
     * @return $this
     */
    public function setOrders(array $orders);
}
