<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model;

use Exception;

class AutopilotException extends Exception
{
    private array $params;

    public function __construct(
        string $url,
        string $method,
        int $status,
        string $payload,
        string $response
    ) {
        parent::__construct($method . ': ' . $url, $status);
        $this->params = [
            'status' => $status,
        ];

        if (!empty($response)) {
            $this->params['response'] = $response;
        }

        if (!empty($payload)) {
            $this->params['payload'] = $payload;
        }
    }

    public function toArray(): array
    {
        return $this->params;
    }
}
