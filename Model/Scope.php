<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model;

use Autopilot\AP3Connector\Api\ConfigScopeInterface;

class Scope implements ConfigScopeInterface
{
    private string $name;
    private int $id;
    private string $type;
    private string $code;
    private bool $connected;
    private int $websiteId;
    /** @var int[] */
    private array $storeIds;

    /**
     * @inheirtDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheirtDoc
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @inheirtDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheirtDoc
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @inheirtDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheirtDoc
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @inheirtDoc
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * @inheirtDoc
     */
    public function setIsConnected(bool $connected)
    {
        $this->connected = $connected;
        return $this;
    }

    /**
     * @inheirtDoc
     */
    public function getWebsiteId(): int
    {
        return $this->websiteId;
    }

    /**
     * @inheirtDoc
     */
    public function toString(): string
    {
        return sprintf("%s:%s:%d", $this->type, $this->code, $this->id);
    }

    /**
     * @inheirtDoc
     */
    public function getStoreIds(): array
    {
        return $this->storeIds;
    }

    /**
     * @inheirtDoc
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @inheirtDoc
     */
    public function setCode(string $code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @inheirtDoc
     */
    public function addStoreId(int $id)
    {
        if (empty($this->storeIds)) {
            $this->storeIds = [];
        }
        $this->storeIds[] = $id;
    }

    /**
     * @inheirtDoc
     */
    public function setWebsiteId(int $id)
    {
        $this->websiteId = $id;
        return $this;
    }

    /**
     * @inheirtDoc
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
        ];
    }

    /**
     * @inheirtDoc
     */
    public function equals(ConfigScopeInterface $scope): bool
    {
        return $this->type === $scope->getType() && $this->code === $scope->getCode();
    }
}
