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
        array $request,
        string $response
    ) {
        parent::__construct(sprintf('%s: %s', $method, $url), $status);
        $this->params = [
            'status' => $status,
        ];

        if (!empty($request)) {
            $this->params['request'] = $request;
        }
        
        if (!empty($response)) {
            $this->params['response'] = $response;
        }
    }

    public function toArray(): array
    {
        return $this->params;
    }
}
