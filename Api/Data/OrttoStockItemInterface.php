<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoStockItemInterface
{
    const QUANTITY = 'quantity';
    const IS_IN_STOCK = 'is_in_stock';
    const IS_SALABLE = 'is_salable';

    /**
     * Set quantity
     *
     * @param float $quantity
     * @return $this
     */
    public function setQuantity($quantity);

    /**
     * Get quantity
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Set is in stock
     *
     * @param bool $isInStock
     * @return $this
     */
    public function setIsInStock($isInStock);

    /**
     * Get is in stock
     *
     * @return bool
     */
    public function getIsInStock();

    /**
     * Set is salable
     *
     * @param bool $isSalable
     * @return $this
     */
    public function setIsSalable($isSalable);

    /**
     * Get is salable
     *
     * @return bool
     */
    public function getIsSalable();
}
