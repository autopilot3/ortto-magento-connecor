<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoOrderExtensionInterface;
use Ortto\Connector\Helper\To;

class OrttoOrderExtension extends DataObject implements OrttoOrderExtensionInterface
{
    /** @inheirtDoc */
    public function setAmazonReferenceId($amazonReferenceId)
    {
        return $this->setData(self::AMAZON_REFERENCE_ID, $amazonReferenceId);
    }
    /** @inheirtDoc */
    public function getAmazonReferenceId()
    {
        return To::int($this->getData(self::AMAZON_REFERENCE_ID));
    }
    /** @inheirtDoc */
    public function setGift($gift)
    {
        return $this->setData(self::GIFT, $gift);
    }
    /** @inheirtDoc */
    public function getGift()
    {
        return $this->getData(self::GIFT);
    }
}
