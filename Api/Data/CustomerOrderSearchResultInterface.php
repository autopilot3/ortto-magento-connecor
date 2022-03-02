<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api\Data;

interface CustomerOrderSearchResultInterface
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
     * @return CustomerOrderInterface[]
     */
    public function getOrders(): array;

    /**
     * @return bool
     */
    public function hasMore(): bool;
}
