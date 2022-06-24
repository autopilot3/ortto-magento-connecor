<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoCountryInterface;

class OrttoCountry extends DataObject implements OrttoCountryInterface
{
    /** @inheirtDoc */
    public function setAbbr2($abbr2)
    {
        return $this->setData(self::ABBR2, $abbr2);
    }

    /** @inheirtDoc */
    public function getAbbr2()
    {
        return (string)$this->getData(self::ABBR2);
    }

    /** @inheirtDoc */
    public function setAbbr3($abbr3)
    {
        return $this->setData(self::ABBR3, $abbr3);
    }

    /** @inheirtDoc */
    public function getAbbr3()
    {
        return (string)$this->getData(self::ABBR3);
    }

    /** @inheirtDoc */
    public function setNameEn($nameEn)
    {
        return $this->setData(self::NAME_EN, $nameEn);
    }

    /** @inheirtDoc */
    public function getNameEn()
    {
        return (string)$this->getData(self::NAME_EN);
    }

    /** @inheirtDoc */
    public function setNameLocal($nameLocal)
    {
        return $this->setData(self::NAME_LOCAL, $nameLocal);
    }

    /** @inheirtDoc */
    public function getNameLocal()
    {
        return (string)$this->getData(self::NAME_LOCAL);
    }
}
