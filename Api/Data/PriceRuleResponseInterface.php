<?php

namespace Ortto\Connector\Api\Data;

interface PriceRuleResponseInterface extends SerializableInterface
{
    /**
     * String constants for property names
     */
    const ID = "id";

    /**
     * Getter for Id.
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Setter for Id.
     *
     * @param int|null $id
     *
     * @return void
     */
    public function setId(int $id): void;
}
