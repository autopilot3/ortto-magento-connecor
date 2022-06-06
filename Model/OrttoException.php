<?php
declare(strict_types=1);

namespace Ortto\Connector\Model;

use Exception;

class OrttoException extends Exception
{
    private array $params;

    public function __construct(
        string $url,
        string $method,
        int $status,
        string $payload,
        string $response
    ) {
        parent::__construct(sprintf('%s (Server Response: %d)', $url, $status), $status);
        $this->params = [
            'status' => $status,
            'method' => $method,
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
