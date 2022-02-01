<?php


namespace Autopilot\AP3Connector\Model;


class ApiException extends \Exception
{
    private string $response;

    public function __construct(string $message, int $status, string $response)
    {
        parent::__construct($message, $status);
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }
}
