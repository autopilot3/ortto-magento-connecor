<?php

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\ListCustomerResponseInterface;
use Ortto\Connector\Helper\To;

class ListCustomerResponse extends DataObject implements ListCustomerResponseInterface
{
    /** @inheirtDoc */
    public function getTotal(): int
    {
        return To::int($this->getData(self::TOTAL));
    }

    /** @inheirtDoc */
    public function setTotal(int $total): void
    {
        $this->setData(self::TOTAL, $total);
    }

    /** @inheirtDoc */
    public function getHasMore(): bool
    {
        return To::bool($this->getData(self::HAS_MORE));
    }

    /** @inheirtDoc */
    public function setHasMore(bool $hasMore)
    {
        $this->setData(self::HAS_MORE, $hasMore);
    }

    /** @inheirtDoc */
    public function setCustomers(array $customers)
    {
        $this->setData(self::CUSTOMERS, $customers);
    }

    /** @inheirtDoc */
    public function getCustomers(): array
    {
        return $this->getData(self::CUSTOMERS) ?? [];
    }
}
