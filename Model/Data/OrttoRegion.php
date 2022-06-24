<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoRegionInterface;

class OrttoRegion extends DataObject implements OrttoRegionInterface
{
    /** @inheirtDoc */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /** @inheirtDoc */
    public function getCode()
    {
        return (string)$this->getData(self::CODE);
    }

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
}
