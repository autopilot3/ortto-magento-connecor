<?php


namespace Autopilot\AP3Connector\Model;


class Scope
{
    private string $name;
    private int $id;
    private string $type;
    private string $code;
    private bool $isUnique;

    public function __construct(string $type, int $id, string $name = "", string $code = "")
    {
        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUniqueName(): string
    {
        if ($this->id < 0) {
            return "";
        }
        if ($this->isUnique) {
            return $this->name;
        }
        return $this->name . '::' . $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return bool
     */
    public function isUnique(): bool
    {
        return $this->isUnique;
    }

    /**
     * @param bool $isUnique
     */
    public function setIsUnique(bool $isUnique): void
    {
        $this->isUnique = $isUnique;
    }
}
