<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoCarrierInterface;
use Ortto\Connector\Helper\To;

class OrttoCarrier extends DataObject implements OrttoCarrierInterface
{
    /** @inheirtDoc */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }
    /** @inheirtDoc */
    public function getId()
    {
        return To::int($this->getData(self::ID));
    }
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
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }
    /** @inheirtDoc */
    public function getTitle()
    {
        return (string)$this->getData(self::TITLE);
    }
    /** @inheirtDoc */
    public function setTrackingNumber($trackingNumber)
    {
        return $this->setData(self::TRACKING_NUMBER, $trackingNumber);
    }
    /** @inheirtDoc */
    public function getTrackingNumber()
    {
        return (string)$this->getData(self::TRACKING_NUMBER);
    }
    /** @inheirtDoc */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
    /** @inheirtDoc */
    public function getCreatedAt()
    {
        return (string)$this->getData(self::CREATED_AT);
    }
}
