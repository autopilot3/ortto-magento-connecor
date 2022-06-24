<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoCountryInterface
{
    const ABBR2 = 'abbr2';
    const ABBR3 = 'abbr3';
    const NAME_EN = 'name_en';
    const NAME_LOCAL = 'name_local';

    /**
    * Set abbr2
    *
    * @param string $abbr2
    * @return $this
    */
    public function setAbbr2($abbr2);

    /**
    * Get abbr2
    *
    * @return string
    */
    public function getAbbr2();

    /**
    * Set abbr3
    *
    * @param string $abbr3
    * @return $this
    */
    public function setAbbr3($abbr3);

    /**
    * Get abbr3
    *
    * @return string
    */
    public function getAbbr3();

    /**
    * Set name en
    *
    * @param string $nameEn
    * @return $this
    */
    public function setNameEn($nameEn);

    /**
    * Get name en
    *
    * @return string
    */
    public function getNameEn();

    /**
    * Set name local
    *
    * @param string $nameLocal
    * @return $this
    */
    public function setNameLocal($nameLocal);

    /**
    * Get name local
    *
    * @return string
    */
    public function getNameLocal();
}
