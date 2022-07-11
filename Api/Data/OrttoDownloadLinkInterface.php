<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoDownloadLinkInterface
{
    const TITLE = 'title';
    const DOWNLOADS = 'downloads';
    const TYPE = 'type';
    const URL = 'url';
    const FILE = 'file';
    const SAMPLE_TYPE = 'sample_type';
    const SAMPLE_URL = 'sample_url';
    const SAMPLE_FILE = 'sample_file';
    const PRICE = 'price';

    /**
    * Set title
    *
    * @param string $title
    * @return $this
    */
    public function setTitle($title);

    /**
    * Get title
    *
    * @return string
    */
    public function getTitle();

    /**
    * Set downloads
    *
    * @param int $downloads
    * @return $this
    */
    public function setDownloads($downloads);

    /**
    * Get downloads
    *
    * @return int
    */
    public function getDownloads();

    /**
    * Set type
    *
    * @param string $type
    * @return $this
    */
    public function setType($type);

    /**
    * Get type
    *
    * @return string
    */
    public function getType();

    /**
    * Set url
    *
    * @param string $url
    * @return $this
    */
    public function setUrl($url);

    /**
    * Get url
    *
    * @return string
    */
    public function getUrl();

    /**
    * Set file
    *
    * @param string $file
    * @return $this
    */
    public function setFile($file);

    /**
    * Get file
    *
    * @return string
    */
    public function getFile();

    /**
    * Set sample type
    *
    * @param string $sampleType
    * @return $this
    */
    public function setSampleType($sampleType);

    /**
    * Get sample type
    *
    * @return string
    */
    public function getSampleType();

    /**
    * Set sample url
    *
    * @param string $sampleUrl
    * @return $this
    */
    public function setSampleUrl($sampleUrl);

    /**
    * Get sample url
    *
    * @return string
    */
    public function getSampleUrl();

    /**
    * Set sample file
    *
    * @param string $sampleFile
    * @return $this
    */
    public function setSampleFile($sampleFile);

    /**
    * Get sample file
    *
    * @return string
    */
    public function getSampleFile();

    /**
    * Set price
    *
    * @param float $price
    * @return $this
    */
    public function setPrice($price);

    /**
    * Get price
    *
    * @return float
    */
    public function getPrice();

    /**
    * Convert object data to array
    *
    * @return array
    */
    public function serializeToArray();
}
