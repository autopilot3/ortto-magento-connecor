<?php

namespace Autopilot\AP3Connector\Api\Data;

interface TrackingDataInterface
{
    /**
     * String constants for property names
     */
    const SCOPE_ID = "scope_id";
    const SCOPE_TYPE = "scope_type";
    const EMAIL = "email";
    const PHONE = "phone";
    const CUSTOMER_ID = "customer_id";
    public const SCOPE = 'scope';
    const PAYLOAD = 'payload';

    /**
     * Getter for ScopeId.
     *
     * @return int
     */
    public function getScopeId(): int;

    /**
     * Setter for ScopeId.
     *
     * @param int $scopeId
     *
     * @return $this
     */
    public function setScopeId(int $scopeId);

    /**
     * Getter for ScopeType.
     *
     * @return string
     */
    public function getScopeType(): string;

    /**
     * Setter for ScopeType.
     *
     * @param string $scopeType
     *
     * @return $this
     */
    public function setScopeType(string $scopeType);

    /**
     * Getter for Email.
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Setter for Email.
     *
     * @param string|null $email
     *
     * @return $this
     */
    public function setEmail(string $email);

    /**
     * Getter for Phone.
     *
     * @return string
     */
    public function getPhone(): string;

    /**
     * Setter for Phone.
     *
     * @param string $phone
     *
     * @return $this
     */
    public function setPhone(string $phone);
}
