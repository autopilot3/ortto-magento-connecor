<?php

namespace Autopilot\AP3Connector\Api;

interface ImportContactResponseInterface
{
    const UPDATED = "updated";
    const CREATED = "created";
    const SKIPPED = "skipped";

    /**
     * @return int
     */
    public function getUpdated(): int;

    /**
     * @return int
     */
    public function getCreated(): int;

    /**
     * @return int
     */
    public function getSkipped(): int;

    public function incr(ImportContactResponseInterface $value): void;

    public function toJSON(): string;
}
