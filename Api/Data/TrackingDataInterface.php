<?php

namespace Ortto\Connector\Api\Data;

use Ortto\Connector\Api\ConfigScopeInterface;

interface TrackingDataInterface
{
    /**
     * String constants for property names
     */
    public const SCOPE = "scope";
    public const EMAIL = "email";
    public const PHONE = "phone";

    /**
     * Getter for Scope.
     *
     * @return ConfigScopeInterface
     */
    public function getScope(): ConfigScopeInterface;

    /**
     * Setter for Scope.
     *
     * @param ConfigScopeInterface $scope
     *
     * @return $this
     */
    public function setScope(ConfigScopeInterface $scope);

    /**
     * Getter for Email.
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Setter for Email.
     *
     * @param string $email
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
