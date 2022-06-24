<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoDownloadLinkInterface;
use Ortto\Connector\Helper\To;

class OrttoDownloadLink extends DataObject implements OrttoDownloadLinkInterface
{

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
    public function setDownloads($downloads)
    {
        return $this->setData(self::DOWNLOADS, $downloads);
    }

    /** @inheirtDoc */
    public function getDownloads()
    {
        return To::int($this->getData(self::DOWNLOADS));
    }

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
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /** @inheirtDoc */
    public function getUrl()
    {
        return (string)$this->getData(self::URL);
    }

    /** @inheirtDoc */
    public function setFile($file)
    {
        return $this->setData(self::FILE, $file);
    }

    /** @inheirtDoc */
    public function getFile()
    {
        return (string)$this->getData(self::FILE);
    }

    /** @inheirtDoc */
    public function setSampleType($sampleType)
    {
        return $this->setData(self::SAMPLE_TYPE, $sampleType);
    }

    /** @inheirtDoc */
    public function getSampleType()
    {
        return (string)$this->getData(self::SAMPLE_TYPE);
    }

    /** @inheirtDoc */
    public function setSampleUrl($sampleUrl)
    {
        return $this->setData(self::SAMPLE_URL, $sampleUrl);
    }

    /** @inheirtDoc */
    public function getSampleUrl()
    {
        return (string)$this->getData(self::SAMPLE_URL);
    }

    /** @inheirtDoc */
    public function setSampleFile($sampleFile)
    {
        return $this->setData(self::SAMPLE_FILE, $sampleFile);
    }

    /** @inheirtDoc */
    public function getSampleFile()
    {
        return (string)$this->getData(self::SAMPLE_FILE);
    }

    /** @inheirtDoc */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /** @inheirtDoc */
    public function getPrice()
    {
        return To::float($this->getData(self::PRICE));
    }
}
