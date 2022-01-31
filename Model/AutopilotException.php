<?php


namespace Autopilot\AP3Connector\Model;


class AutopilotException extends \Exception
{
    private string $url;
    private string $method;
    private ?array $params;
    private ?\Exception $error;

    public function __construct(string $message, string $url, string $method, int $code, array $params = null)
    {
        parent::__construct($message, $code);
        $this->url = $url;
        $this->method = $method;
        $this->params = $params;
        $this->error = null;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @return \Exception|null
     */
    public function getError(): ?\Exception
    {
        return $this->error;
    }

    /**
     * @param \Exception|null $error
     */
    public function setError(?\Exception $error): void
    {
        $this->error = $error;
    }
}
