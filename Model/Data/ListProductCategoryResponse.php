<?php

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\ListProductCategoryResponseInterface;
use Ortto\Connector\Helper\To;

class ListProductCategoryResponse extends DataObject implements ListProductCategoryResponseInterface
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
    public function setCategories(array $categories)
    {
        $this->setData(self::CATEGORIES, $categories);
    }

    /** @inheirtDoc */
    public function getCategories()
    {
        return $this->getData(self::CATEGORIES) ?? [];
    }
}
