<?php


namespace Autopilot\AP3Connector\Model;

use Autopilot\AP3Connector\Api\ImportContactResponseInterface as ResponseInterface;

class ImportContactResponse implements ResponseInterface
{
    private int $updated;
    private int $created;
    private int $skipped;

    public function __construct(array $data = [])
    {
        $this->updated = 0;
        $this->created = 0;
        $this->skipped = 0;
        if (isset($data[ResponseInterface::CREATED])) {
            $this->created = $data[ResponseInterface::CREATED];
        }
        if (isset($data[ResponseInterface::UPDATED])) {
            $this->updated = $data[ResponseInterface::UPDATED];
        }
        if (isset($data[ResponseInterface::SKIPPED])) {
            $this->skipped = $data[ResponseInterface::SKIPPED];
        }
    }

    /**
     * @return int
     */
    public function getUpdated(): int
    {
        return $this->updated;
    }

    /**
     * @return int
     */
    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @return int
     */
    public function getSkipped(): int
    {
        return $this->skipped;
    }

    /**
     * @param ResponseInterface $value
     * @return void
     */
    public function incr(ResponseInterface $value): void
    {
        if (empty($value)) {
            return;
        }
        $this->updated += $value->getUpdated();
        $this->created += $value->getCreated();
        $this->skipped += $value->getSkipped();
    }

    public function toJSON(): string
    {
        return json_encode([
            ResponseInterface::CREATED => $this->getCreated(),
            ResponseInterface::UPDATED => $this->getUpdated(),
            ResponseInterface::SKIPPED => $this->getSkipped(),
        ]);
    }
}
