<?php

namespace Autopilot\AP3Connector\Api\Data;

use DateTime;

interface SyncJobInterface
{
    /**
     * String constants for property names
     */
    const ID = "id";
    const CATEGORY = "category";
    const SCOPE_TYPE = "scope_type";
    const SCOPE_ID = "scope_id";
    const STATUS = "status";
    const CREATED_AT = "created_at";
    const FINISHED_AT = "finished_at";
    const COUNT = "count";
    const ERROR = "error";

    /**
     * Getter for Id.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Setter for Id.
     *
     * @param mixed
     *
     * @return $this
     */
    public function setId($value);

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
}
