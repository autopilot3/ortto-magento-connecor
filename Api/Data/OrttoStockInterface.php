<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoStockInterface
{
    const NAME = 'name';
    const QUANTITY = 'quantity';
    const IS_MANAGE = 'is_manage';

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

    /**
    * Set quantity
    *
    * @param float $quantity
    * @return $this
    */
    public function setQuantity($quantity);

    /**
    * Get quantity
    *
    * @return float
    */
    public function getQuantity();

    /**
    * Set is manage
    *
    * @param bool $isManage
    * @return $this
    */
    public function setIsManage($isManage);

    /**
    * Get is manage
    *
    * @return bool
    */
    public function getIsManage();

    /**
    * Convert object data to array
    *
    * @return array
    */
    public function serializeToArray();
}
