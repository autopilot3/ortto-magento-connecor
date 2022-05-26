<?php

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\CouponResponseInterface;
use Ortto\Connector\Helper\To;

class CouponResponse extends DataObject implements CouponResponseInterface
{
    /** @inerhitDoc */
    public function getId(): int
    {
        return To::int($this->getData(self::ID));
    }

    /** @inerhitDoc */
    public function setId(int $id)
    {
        $this->setData(self::ID, $id);
        return $this;
    }

    /** @inerhitDoc */
    public function getCode(): string
    {
        return (string)$this->getData(self::CODE);
    }

    /** @inerhitDoc */
    public function setCode(string $code)
    {
        $this->setData(self::CODE, $code);
        return $this;
    }
}
