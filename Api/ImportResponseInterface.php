<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface ImportResponseInterface
{
    const SKIPPED = 'skipped';
    const CREATED = 'created';
    const UPDATED = 'updated';
    const METADATA = 'metadata';

    /**
     * @return int
     */
    public function getCreatedTotal(): int;

    /**
     * @return int
     */
    public function getUpdatedTotal(): int;

    /**
     * @return int
     */
    public function getSkippedTotal(): int;

    /**
     * @return array
     */
    public function getMetadata(): array;

    /**
     * @param ImportResponseInterface $value
     * @return void
     */
    public function incr(ImportResponseInterface $value): void;

    /**
     * @return string
     */
    public function toJSON(): string;
}
