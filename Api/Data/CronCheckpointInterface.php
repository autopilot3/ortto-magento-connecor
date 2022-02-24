<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api\Data;

use DateTime;

interface CronCheckpointInterface
{
    /**
     * String constants for property names
     */
    const ID = "id";
    const CATEGORY = "category";
    const SCOPE_TYPE = "scope_type";
    const SCOPE_ID = "scope_id";
    const LAST_CHECKED_AT = "last_checked_at";

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
     * Getter for CheckedAt.
     *
     * @return DateTime|null
     */
    public function getCheckedAt();

    /**
     * Setter for CheckedAt.
     *
     * @param DateTime $checkedAt
     *
     * @return $this
     */
    public function setCheckedAt(DateTime $checkedAt);
}
