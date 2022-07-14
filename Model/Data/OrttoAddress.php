<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoAddressInterface;

class OrttoAddress extends DataObject implements OrttoAddressInterface
{
    /** @inheirtDoc */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /** @inheirtDoc */
    public function getType()
    {
        return (string)$this->getData(self::TYPE);
    }

    /** @inheirtDoc */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /** @inheirtDoc */
    public function getCity()
    {
        return (string)$this->getData(self::CITY);
    }

    /** @inheirtDoc */
    public function setCompany($company)
    {
        return $this->setData(self::COMPANY, $company);
    }

    /** @inheirtDoc */
    public function getCompany()
    {
        return (string)$this->getData(self::COMPANY);
    }

    /** @inheirtDoc */
    public function setCountryCode($countryCode)
    {
        return $this->setData(self::COUNTRY_CODE, $countryCode);
    }

    /** @inheirtDoc */
    public function getCountryCode()
    {
        return (string)$this->getData(self::COUNTRY_CODE);
    }

    /** @inheirtDoc */
    public function setFirstName($firstName)
    {
        return $this->setData(self::FIRST_NAME, $firstName);
    }

    /** @inheirtDoc */
    public function getFirstName()
    {
        return (string)$this->getData(self::FIRST_NAME);
    }

    /** @inheirtDoc */
    public function setLastName($lastName)
    {
        return $this->setData(self::LAST_NAME, $lastName);
    }

    /** @inheirtDoc */
    public function getLastName()
    {
        return (string)$this->getData(self::LAST_NAME);
    }

    /** @inheirtDoc */
    public function setMiddleName($middleName)
    {
        return $this->setData(self::MIDDLE_NAME, $middleName);
    }

    /** @inheirtDoc */
    public function getMiddleName()
    {
        return (string)$this->getData(self::MIDDLE_NAME);
    }

    /** @inheirtDoc */
    public function setPostCode($postCode)
    {
        return $this->setData(self::POST_CODE, $postCode);
    }

    /** @inheirtDoc */
    public function getPostCode()
    {
        return (string)$this->getData(self::POST_CODE);
    }

    /** @inheirtDoc */
    public function setPrefix($prefix)
    {
        return $this->setData(self::PREFIX, $prefix);
    }

    /** @inheirtDoc */
    public function getPrefix()
    {
        return (string)$this->getData(self::PREFIX);
    }

    /** @inheirtDoc */
    public function setSuffix($suffix)
    {
        return $this->setData(self::SUFFIX, $suffix);
    }

    /** @inheirtDoc */
    public function getSuffix()
    {
        return (string)$this->getData(self::SUFFIX);
    }

    /** @inheirtDoc */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /** @inheirtDoc */
    public function getRegion()
    {
        return (string)$this->getData(self::REGION);
    }

    /** @inheirtDoc */
    public function setStreetLines(array $streetLines)
    {
        return $this->setData(self::STREET_LINES, $streetLines);
    }

    /** @inheirtDoc */
    public function getStreetLines(): array
    {
        return $this->getData(self::STREET_LINES) ?? [];
    }

    /** @inheirtDoc */
    public function setVat($vat)
    {
        return $this->setData(self::VAT, $vat);
    }

    /** @inheirtDoc */
    public function getVat()
    {
        return (string)$this->getData(self::VAT);
    }

    /** @inheirtDoc */
    public function setPhone($phone)
    {
        return $this->setData(self::PHONE, $phone);
    }

    /** @inheirtDoc */
    public function getPhone()
    {
        return (string)$this->getData(self::PHONE);
    }

    /** @inheirtDoc */
    public function setFax($fax)
    {
        return $this->setData(self::FAX, $fax);
    }

    /** @inheirtDoc */
    public function getFax()
    {
        return (string)$this->getData(self::FAX);
    }

    /** @inheirtDoc */
    public function serializeToArray()
    {
        if ($this == null) {
            return null;
        }
        $result=[];
        $result[self::TYPE] = $this->getType();
        $result[self::CITY] = $this->getCity();
        $result[self::COMPANY] = $this->getCompany();
        $result[self::COUNTRY_CODE] = $this->getCountryCode();
        $result[self::FIRST_NAME] = $this->getFirstName();
        $result[self::LAST_NAME] = $this->getLastName();
        $result[self::MIDDLE_NAME] = $this->getMiddleName();
        $result[self::POST_CODE] = $this->getPostCode();
        $result[self::PREFIX] = $this->getPrefix();
        $result[self::SUFFIX] = $this->getSuffix();
        $result[self::REGION] = $this->getRegion();
        $result[self::STREET_LINES] = $this->getStreetLines();
        $result[self::VAT] = $this->getVat();
        $result[self::PHONE] = $this->getPhone();
        $result[self::FAX] = $this->getFax();
        return $result;
    }
}
