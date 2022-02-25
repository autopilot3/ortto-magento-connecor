<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model\Data;

use Autopilot\AP3Connector\Api\CustomerReaderInterface;
use Autopilot\AP3Connector\Api\Data\CustomerDataInterface;
use Autopilot\AP3Connector\Api\Data\ReadCustomerResultInterface;
use Magento\Framework\DataObject;

class ReadCustomerResult extends DataObject implements ReadCustomerResultInterface
{
    private int $total;
    private int $nextPage;
    /**
     * @var CustomerDataInterface[]
     */
    private array $customers;

    /**
     * @param array $customers
     * @param int $page
     * @param int $total
     * @param CustomerDataInterface[] $data
     */
    public function __construct(array $customers, int $page, int $total, array $data = [])
    {
        parent::__construct($data);
        $this->total = $total;
        $this->nextPage = $page + 1;
        $this->customers = $customers;
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
    public function getCustomers(): array
    {
        return $this->customers;
    }

    /**
     * @inheirtDoc
     */
    public function hasMore(): bool
    {
        return count($this->customers) >= CustomerReaderInterface::PAGE_SIZE;
    }
}
