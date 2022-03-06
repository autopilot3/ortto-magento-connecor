<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Model;

use Autopilot\AP3Connector\Api\Data\CustomerOrderInterface;
use Magento\Framework\DataObject;

class CustomerOrder extends DataObject implements CustomerOrderInterface
{
    /**
     * @inheirtDoc
     */
    public function getCustomerId(): int
    {
        return (int)$this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheirtDoc
     */
    public function setCustomerId(int $customerId)
    {
        $this->setData(self::CUSTOMER_ID, $customerId);
        return $this;
    }

    /**
     * @inheirtDoc
     */
    public function getCustomerEmail(): string
    {
        return (string)$this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * @inheirtDoc
     */
    public function setCustomerEmail(string $email)
    {
        $this->setData(self::CUSTOMER_EMAIL, $email);
        return $this;
    }

    /**
     * @inheirtDoc
     */
    public function getOrders(): array
    {
        return $this->getData(self::CUSTOMER_ORDERS);
    }

    /**
     * @inheirtDoc
     */
    public function setOrders(array $orders)
    {
        $this->setData(self::CUSTOMER_ORDERS, $orders);
        return $this;
    }
}
