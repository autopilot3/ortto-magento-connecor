<?php

namespace Ortto\Connector\Api\Data;

interface CouponResponseInterface extends SerializableInterface
{
    /**
     * String constants for property names
     */
    const ID = "id";
    const CODE = "code";

    /**
     * Getter for Id.
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Setter for Id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId(int $id);

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
     * @return $this
     */
    public function setCode(string $code);

}
