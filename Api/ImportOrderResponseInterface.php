<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

interface ImportOrderResponseInterface
{
    const PROCESSED = "processed";
    const SKIPPED = "skipped";
    const ORDERS = "orders";

    /**
     * @return int
     */
    public function getProcessedTotal(): int;

    /**
     * @return int
     */
    public function getOrdersTotal(): int;

    /**
     * @return int
     */
    public function getSkippedTotal(): int;

    /**
     * @param ImportOrderResponseInterface $value
     * @return void
     */
    public function incr(ImportOrderResponseInterface $value): void;

    /**
     * @return void
     */
    public function incrSkipped(): void;

    /**
     * @return string
     */
    public function toJSON(): string;
}
