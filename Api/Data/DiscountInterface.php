<?php

namespace Ortto\Connector\Api\Data;

interface DiscountInterface extends SerializableInterface
{
    /**
     * String constants for property names
     */
    const CODE = "code";
    const RULE_ID = "rule_id";

    /**
     * Getter for Code.
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Setter for Code.
     *
     * @param string $code
     *
     * @return void
     */
    public function setCode(string $code): void;

    /**
     * Getter for RuleId.
     *
     * @return int
     */
    public function getRuleId(): int;

    /**
     * Setter for RuleId.
     *
     * @param int $ruleId
     *
     * @return void
     */
    public function setRuleId(int $ruleId): void;

    /**
     * @return string
     */
    public function validate(): string;
}
