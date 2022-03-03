<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model;

use Autopilot\AP3Connector\Api\ImportOrderResponseInterface as ResponseInterface;

class ImportOrderResponse implements ResponseInterface
{
    private int $skippedTotal;
    private int $processedTotal;
    private int $ordersTotal;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->processedTotal = 0;
        $this->skippedTotal = 0;
        $this->ordersTotal = 0;
        if (isset($data[ResponseInterface::PROCESSED])) {
            $this->processedTotal = $data[ResponseInterface::PROCESSED];
        }
        if (isset($data[ResponseInterface::SKIPPED])) {
            $this->skippedTotal = $data[ResponseInterface::SKIPPED];
        }
        if (isset($data[ResponseInterface::ORDERS])) {
            $this->ordersTotal = $data[ResponseInterface::ORDERS];
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
    public function getProcessedTotal(): int
    {
        return $this->processedTotal;
    }

    /**
     * @inheirtDoc
     */
    public function getOrdersTotal(): int
    {
        return $this->ordersTotal;
    }

    /**
     * @inheirtDoc
     */
    public function incr(ResponseInterface $value): void
    {
        if (empty($value)) {
            return;
        }
        $this->processedTotal += $value->getProcessedTotal();
        $this->skippedTotal += $value->getSkippedTotal();
        $this->ordersTotal += $value->getOrdersTotal();
    }

    /**
     * @inheirtDoc
     */
    public function incrSkipped(): void
    {
        $this->skippedTotal++;
    }

    /**
     * @inheirtDoc
     */
    public function toJSON(): string
    {
        return json_encode([
            ResponseInterface::PROCESSED => $this->getProcessedTotal(),
            ResponseInterface::SKIPPED => $this->getSkippedTotal(),
            ResponseInterface::ORDERS => $this->getOrdersTotal(),
        ]);
    }
}
