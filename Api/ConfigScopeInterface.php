<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

interface ConfigScopeInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id);

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string $code
     * @return $this
     */
    public function setCode(string $code);

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * @param bool $connected
     * @return $this
     */
    public function setIsConnected(bool $connected);

    /**
     * Returns all the store IDs under a website if scope type == 'website', otherwise (type == 'store')
     * only the ID of the current store will be included.
     *
     * @return int[]
     */
    public function getStoreIds(): array;

    /**
     * @param int $id
     * @return $this
     */
    public function addStoreId(int $id);

    public function equals(ConfigScopeInterface $scope): bool;

    /**
     * @return int
     */
    public function getWebsiteId(): int;

    /**
     * @param int $id
     * @return $this
     */
    public function setWebsiteId(int $id);

    /**
     * @return string
     */
    public function toString(): string;

    /**
     * @return string
     */
    public function getBaseURL(): string;

    /**
     * @param string $url
     * @return $this
     */
    public function setBaseURL(string $url);
}
