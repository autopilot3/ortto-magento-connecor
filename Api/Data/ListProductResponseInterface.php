<?php

namespace Ortto\Connector\Api\Data;

interface ListProductResponseInterface
{
    /**
     * String constants for property names
     */
    const TOTAL = "total";
    const PRODUCTS = "products";
    const HAS_MORE = "has_more";
    const CATEGORIES = "categories";

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
     * @param \Ortto\Connector\Api\Data\OrttoProductInterface[] $products
     * @return void
     */
    public function setProducts(array $products);

    /**
     * @return \Ortto\Connector\Api\Data\OrttoProductInterface[]
     */
    public function getProducts();

    /**
     * @param \Ortto\Connector\Api\Data\OrttoProductCategoryInterface[] $categories
     * @return void
     */
    public function setCategories(array $categories);

    /**
     * @return \Ortto\Connector\Api\Data\OrttoProductCategoryInterface[]
     */
    public function getCategories();
}
