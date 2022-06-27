<?php

namespace Ortto\Connector\Api\Data;

interface ListRestockSubscriptionResponseInterface
{
    /**
     * String constants for property names
     */
    const TOTAL = "total";
    const SUBSCRIPTIONS = "subscriptions";
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
     * @param \Ortto\Connector\Api\Data\OrttoRestockSubscriptionInterface[] $subscriptions
     * @return void
     */
    public function setSubscriptions(array $subscriptions);

    /**
     * @return \Ortto\Connector\Api\Data\OrttoRestockSubscriptionInterface[]
     */
    public function getSubscriptions();
}
