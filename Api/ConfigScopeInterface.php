<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Api\Data\SerializableInterface;

interface ConfigScopeInterface extends SerializableInterface
{
    const  NAME = 'name';
    const ID = 'id';
    const TYPE = 'type';
    const CODE = 'code';
    const IS_CONNECTED = 'is_connected';
    const WEBSITE_ID = 'website_id';
    const URL = 'url';
    const STORE_IDS = 'store_ids';

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
