<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api\Data;

/**
 * Interface AppConfigInterface
 * @api
 */
interface AppConfigInterface extends SerializableInterface
{
    public const SCOPE_ID = 'scope_id';
    public const SCOPE_TYPE = 'scope_type';
    public const KEYS = 'keys';

    /**
     * @param string[] $keys
     * @return $this
     */
    public function setKeys(array $keys): AppConfigInterface;

    /**
     * @return string[]
     */
    public function getKeys(): array;

    /**
     * @param int $scopeId
     * @return $this
     */
    public function setScopeId(int $scopeId): AppConfigInterface;

    /**
     * @return int
     */
    public function getScopeId(): int;

    /**
     * @param string $scopeType
     * @return $this
     */
    public function setScopeType(string $scopeType): AppConfigInterface;

    /**
     * @return string
     */
    public function getScopeType(): string;
}
