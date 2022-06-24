<?php

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\ListProductResponseInterface;
use Ortto\Connector\Helper\To;

class ListProductResponse extends DataObject implements ListProductResponseInterface
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
    public function setProducts(array $products)
    {
        $this->setData(self::PRODUCTS, $products);
    }

    /** @inheirtDoc */
    public function getProducts(): array
    {
        return $this->getData(self::PRODUCTS) ?? [];
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
