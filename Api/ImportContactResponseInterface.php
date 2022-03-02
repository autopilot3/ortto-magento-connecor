<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

interface ImportContactResponseInterface
{
    const PROCESSED = "processed";
    const SKIPPED = "skipped";

    /**
     * @return array
     */
    public function getContacts(): array;

    /**
     * @return int
     */
    public function getContactsTotal(): int;

    /**
     * @return int
     */
    public function getSkippedTotal(): int;

    public function incr(ImportContactResponseInterface $value): void;

    public function toJSON(): string;
}
