<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Model\Data;

use Autopilot\AP3Connector\Api\Data\CouponInterface;
use Magento\Framework\DataObject;

class Coupon extends DataObject implements CouponInterface
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->_getData(self::DATA_TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title): CouponInterface
    {
        return $this->setData(self::DATA_TITLE, $title);
    }
}
