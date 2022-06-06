<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

use DateTime;

interface SyncJobInterface
{
    /**
     * String constants for property names
     */
    const ENTITY_ID = "entity_id";
    const CATEGORY = "category";
    const SCOPE_TYPE = "scope_type";
    const SCOPE_ID = "scope_id";
    const STATUS = "status";
    const CREATED_AT = "created_at";
    const STARTED_AT = "started_at";
    const FINISHED_AT = "finished_at";
    const COUNT = "count";
    const TOTAL = "total";
    const ERROR = "error";
    const METADATA = "metadata";

    /**
     * Getter for Id.
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Setter for Id.
     *
     * @param int
     *
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Getter for Category.
     *
     * @return string
     */
    public function getCategory();

    /**
     * Setter for Category.
     *
     * @param string $category
     *
     * @return $this
     */
    public function setCategory(string $category);

    /**
     * Getter for ScopeType.
     *
     * @return string
     */
    public function getScopeType();

    /**
     * Setter for ScopeType.
     *
     * @param string $scopeType
     *
     * @return $this
     */
    public function setScopeType(string $scopeType);

    /**
     * Getter for ScopeId.
     *
     * @return int
     */
    public function getScopeId();

    /**
     * Setter for ScopeId.
     *
     * @param int $scopeId
     *
     * @return $this
     */
    public function setScopeId(int $scopeId);

    /**
     * Getter for Status.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Setter for Status.
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus(string $status);

    /**
     * Getter for CreatedAt.
     *
     * @return DateTime
     */
    public function getCreatedAt();

    /**
     * Setter for CreatedAt.
     *
     * @param DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(DateTime $createdAt);

    /**
     * Getter for FinishedAt.
     *
     * @return DateTime|null
     */
    public function getFinishedAt();

    /**
     * Setter for FinishedAt.
     *
     * @param DateTime|null $finishedAt
     *
     * @return $this
     */
    public function setFinishedAt(DateTime $finishedAt);

    /**
     * Getter for StartedAt.
     *
     * @return DateTime|null
     */
    public function getStartedAt();

    /**
     * Setter for StartedAt.
     *
     * @param DateTime|null $startedAt
     *
     * @return $this
     */
    public function setStartedAt(DateTime $startedAt);

    /**
     * Getter for Count.
     *
     * @return int
     */
    public function getCount();

    /**
     * Setter for Count.
     *
     * @param int $count
     *
     * @return $this
     */
    public function setCount(int $count);

    /**
     * Getter for Total.
     *
     * @return int
     */
    public function getTotal();

    /**
     * Setter for Total.
     *
     * @param int $total
     *
     * @return $this
     */
    public function setTotal(int $total);

    /**
     * Getter for Error.
     *
     * @return string|null
     */
    public function getError();

    /**
     * Setter for Error.
     *
     * @param string|null $error
     *
     * @return $this
     */
    public function setError(?string $error);

    /**
     * Getter for Metadata.
     *
     * @return string|null
     */
    public function getMetadata();

    /**
     * Setter for Metadata.
     *
     * @param string|null $metadata
     *
     * @return $this
     */
    public function setMetadata(?string $metadata);
}
