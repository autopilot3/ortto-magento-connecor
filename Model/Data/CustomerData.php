<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Model\Data;

use Autopilot\AP3Connector\Api\Data\CustomerDataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Sales\Api\Data\OrderInterface;

class CustomerData implements CustomerDataInterface
{
    private CustomerInterface $customer;
    /**
     * @var OrderInterface[]
     */
    private array $orders;

    /**
     * @param CustomerInterface $customer
     * @param OrderInterface[] $orders
     */
    public function __construct(CustomerInterface $customer, array $orders)
    {
        $this->customer = $customer;
        $this->orders = $orders;
    }

    /**
     * @inheritDoc
     */
    public function getCustomer(): CustomerInterface
    {
        return $this->customer;
    }

    /**
     * @inheritDoc
     */
    public function getOrders(): array
    {
        return $this->orders;
    }
}
