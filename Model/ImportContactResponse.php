<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model;

use Autopilot\AP3Connector\Api\ImportContactResponseInterface as ResponseInterface;

class ImportContactResponse implements ResponseInterface
{
    private array $contacts;
    private int $totalSkipped;
    private int $contactsTotal;

    public function __construct(array $data = [])
    {
        $this->contacts = [];
        $this->contactsTotal = 0;
        $this->totalSkipped = 0;
        if (isset($data[ResponseInterface::PROCESSED])) {
            $this->contacts = $data[ResponseInterface::PROCESSED];
            $this->contactsTotal = count($this->contacts);
        }
        if (isset($data[ResponseInterface::SKIPPED])) {
            $this->totalSkipped = $data[ResponseInterface::SKIPPED];
        }
    }

    /**
     * @inheirtDoc
     */
    public function getContacts(): array
    {
        return $this->contacts;
    }

    /**
     * @inheirtDoc
     */
    public function getSkippedTotal(): int
    {
        return $this->totalSkipped;
    }

    /**
     * @inheirtDoc
     */
    public function incr(ResponseInterface $value): void
    {
        if (empty($value)) {
            return;
        }
        $this->contactsTotal += $value->getContactsTotal();
        $this->totalSkipped += $value->getSkippedTotal();
    }

    /**
     * @inheirtDoc
     */
    public function toJSON(): string
    {
        return json_encode([
            ResponseInterface::PROCESSED => $this->getContactsTotal(),
            ResponseInterface::SKIPPED => $this->getSkippedTotal(),
        ]);
    }

    public function getContactsTotal(): int
    {
        return $this->contactsTotal;
    }
}
