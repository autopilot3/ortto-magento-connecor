<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api\Data;

interface ReadCustomerResultInterface
{
    /**
     * @return int
     */
    public function getTotal(): int;

    /**
     * @return int
     */
    public function getNextPage(): int;

    /**
     * @return CustomerDataInterface[]
     */
    public function getCustomers(): array;

    /**
     * @return bool
     */
    public function hasMore(): bool;
}
