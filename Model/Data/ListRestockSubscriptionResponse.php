<?php

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\ListRestockSubscriptionResponseInterface;
use Ortto\Connector\Helper\To;

class ListRestockSubscriptionResponse extends DataObject implements ListRestockSubscriptionResponseInterface
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
    public function setItems(array $items)
    {
        $this->setData(self::ITEMS, $items);
    }

    /** @inheirtDoc */
    public function getItems()
    {
        return $this->getData(self::ITEMS) ?? [];
    }
}
