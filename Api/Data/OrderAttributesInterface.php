<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api\Data;

use DateTime;

interface OrderAttributesInterface
{
    public const CANCELED_AT = 'canceled_at';
    public const ORDER_ID = 'order_id';

    /**
     * @return DateTime|null
     */
    public function getCanceledAt();

    /**
     * @param DateTime $dateTime
     * @return $this
     */
    public function setCanceledAt(DateTime $dateTime);
}
