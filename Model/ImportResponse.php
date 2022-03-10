<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model;

use Autopilot\AP3Connector\Api\ImportResponseInterface;

class ImportResponse implements ImportResponseInterface
{
    private int $skippedTotal;
    private int $updatedTotal;
    private int $createdTotal;
    private array $metadata;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->updatedTotal = 0;
        $this->skippedTotal = 0;
        $this->createdTotal = 0;
        $this->metadata = [];
        if (isset($data[ImportResponseInterface::UPDATED])) {
            $this->updatedTotal = $data[ImportResponseInterface::UPDATED];
        }
        if (isset($data[ImportResponseInterface::CREATED])) {
            $this->createdTotal = $data[ImportResponseInterface::CREATED];
        }
        if (isset($data[ImportResponseInterface::SKIPPED])) {
            $this->skippedTotal = $data[ImportResponseInterface::SKIPPED];
        }
        if (isset($data[ImportResponseInterface::METADATA])) {
            $this->metadata = $data[ImportResponseInterface::METADATA];
        }
    }

    /**
     * @inheirtDoc
     */
    public function getSkippedTotal(): int
    {
        return $this->skippedTotal;
    }

    /**
     * @inheirtDoc
     */
    public function getCreatedTotal(): int
    {
        return $this->createdTotal;
    }

    public function getUpdatedTotal(): int
    {
        return $this->updatedTotal;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @inheirtDoc
     */
    public function incr(ImportResponseInterface $value): void
    {
        if (empty($value)) {
            return;
        }
        $this->updatedTotal += $value->getUpdatedTotal();
        $this->skippedTotal += $value->getSkippedTotal();
        $this->createdTotal += $value->getCreatedTotal();
    }

    /**
     * @inheirtDoc
     */
    public function toJSON(): string
    {
        return json_encode([
            ImportResponseInterface::CREATED => $this->getCreatedTotal(),
            ImportResponseInterface::UPDATED => $this->getUpdatedTotal(),
            ImportResponseInterface::SKIPPED => $this->getSkippedTotal(),
        ]);
    }
}
