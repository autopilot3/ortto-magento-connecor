<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoStockInterface;
use Ortto\Connector\Helper\To;

class OrttoStock extends DataObject implements OrttoStockInterface
{
    /** @inheirtDoc */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /** @inheirtDoc */
    public function getName()
    {
        return (string)$this->getData(self::NAME);
    }

    /** @inheirtDoc */
    public function setQuantity($quantity)
    {
        return $this->setData(self::QUANTITY, $quantity);
    }

    /** @inheirtDoc */
    public function getQuantity()
    {
        return To::float($this->getData(self::QUANTITY));
    }

    /** @inheirtDoc */
    public function setIsManage($isManage)
    {
        return $this->setData(self::IS_MANAGE, $isManage);
    }

    /** @inheirtDoc */
    public function getIsManage()
    {
        return To::bool($this->getData(self::IS_MANAGE));
    }

    /** @inheirtDoc */
    public function serializeToArray()
    {
        if ($this == null) {
            return null;
        }
        $result=[];
        $result[self::NAME] = $this->getName();
        $result[self::QUANTITY] = $this->getQuantity();
        $result[self::IS_MANAGE] = $this->getIsManage();
        return $result;
    }
}
