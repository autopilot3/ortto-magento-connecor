<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoOrderExtensionInterface
{
    const AMAZON_REFERENCE_ID = 'amazon_reference_id';
    const GIFT = 'gift';

    /**
    * Set amazon reference id
    *
    * @param int $amazonReferenceId
    * @return $this
    */
    public function setAmazonReferenceId($amazonReferenceId);

    /**
    * Get amazon reference id
    *
    * @return int
    */
    public function getAmazonReferenceId();
    /**
    * Set gift
    *
    * @param \Ortto\Connector\Api\Data\OrttoGiftInterface $gift
    * @return $this
    */
    public function setGift($gift);

    /**
    * Get gift
    *
    * @return \Ortto\Connector\Api\Data\OrttoGiftInterface
    */
    public function getGift();
}
