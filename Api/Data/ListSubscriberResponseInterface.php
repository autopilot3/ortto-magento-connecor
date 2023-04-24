<?php

namespace Ortto\Connector\Api\Data;

interface ListSubscriberResponseInterface
{
    /**
     * String constants for property names
     */
    const TOTAL = "total";
    const ITEMS = "items";
    const HAS_MORE = "has_more";

    /**
     * Getter for Total.
     *
     * @return int
     */
    public function getTotal(): int;

    /**
     * Setter for Total.
     *
     * @param int $total
     *
     * @return void
     */
    public function setTotal(int $total): void;

    /**
     * Getter for has more.
     *
     * @return bool
     */
    public function getHasMore(): bool;

    /**
     * Setter for has more.
     *
     * @param bool $hasMore
     *
     * @return void
     */
    public function setHasMore(bool $hasMore);

    /**
     * @param \Ortto\Connector\Api\Data\OrttoSubscriberInterface[] $items
     * @return void
     */
    public function setItems(array $items);

    /**
     * @return \Ortto\Connector\Api\Data\OrttoSubscriberInterface[]
     */
    public function getItems();
}
