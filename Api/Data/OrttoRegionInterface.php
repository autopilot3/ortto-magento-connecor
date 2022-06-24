<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoRegionInterface
{
    const CODE = 'code';
    const NAME = 'name';

    /**
    * Set code
    *
    * @param string $code
    * @return $this
    */
    public function setCode($code);

    /**
    * Get code
    *
    * @return string
    */
    public function getCode();
    /**
    * Set name
    *
    * @param string $name
    * @return $this
    */
    public function setName($name);

    /**
    * Get name
    *
    * @return string
    */
    public function getName();
}
