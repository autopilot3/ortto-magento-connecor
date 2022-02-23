<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api\Data;

use Magento\Customer\Api\Data\CustomerInterface;

interface ReadCustomerResultInterface
{
    /**
     * @return int
     */
    public function getTotal(): int;

    /**
     * @return int
     */
    public function getCurrentPage(): int;

    /**
     * @return int
     */
    public function getPageSize(): int;

    /**
     * @return CustomerInterface[]
     */
    public function getCustomers(): array;

    /**
     * @return bool
     */
    public function hasMore(): bool;
}
