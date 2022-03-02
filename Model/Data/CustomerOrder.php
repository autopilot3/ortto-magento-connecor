<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Model\Data;

use Autopilot\AP3Connector\Api\Data\CustomerOrderInterface;
use Magento\Sales\Api\Data\OrderInterface;

class CustomerOrder implements CustomerOrderInterface
{
    /**
     * @var OrderInterface[]
     */
    private array $orders;
    private int $customerId;
    private string $customerEmail;

    /**
     * @param int $customerId
     * @param string $customerEmail
     * @param array $orders
     */
    public function __construct(int $customerId, string $customerEmail, array $orders)
    {
        $this->orders = $orders;
        $this->customerId = $customerId;
        $this->customerEmail = $customerEmail;
    }

    /**
     * @inheritDoc
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }
}
