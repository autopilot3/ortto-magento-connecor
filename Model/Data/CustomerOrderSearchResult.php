<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model\Data;

use Autopilot\AP3Connector\Api\CustomerRepoInterface;
use Autopilot\AP3Connector\Api\Data\CustomerOrderInterface;
use Autopilot\AP3Connector\Api\Data\CustomerOrderSearchResultInterface;
use Magento\Framework\DataObject;

class CustomerOrderSearchResult extends DataObject implements CustomerOrderSearchResultInterface
{
    private int $total;
    private int $nextPage;
    /**
     * @var CustomerOrderInterface[]
     */
    private array $customerOrders;

    /**
     * @param array $customers
     * @param int $page
     * @param int $total
     * @param CustomerOrderInterface[] $data
     */
    public function __construct(array $customers, int $page, int $total, array $data = [])
    {
        parent::__construct($data);
        $this->total = $total;
        $this->nextPage = $page + 1;
        $this->customerOrders = $customers;
    }

    /**
     * @inheirtDoc
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @inheirtDoc
     */
    public function getNextPage(): int
    {
        return $this->nextPage;
    }

    /**
     * @inheirtDoc
     */
    public function hasMore(): bool
    {
        return count($this->customerOrders) >= CustomerRepoInterface::ORDER_PAGE_SIZE;
    }

    /**
     * @inheirtDoc
     */
    public function getOrders(): array
    {
        return $this->customerOrders;
    }
}
