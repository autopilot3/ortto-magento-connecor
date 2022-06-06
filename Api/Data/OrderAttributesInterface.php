<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

use DateTime;

interface OrderAttributesInterface
{
    public const ENTITY_ID = 'entity_id';
    public const CANCELED_AT = 'canceled_at';
    public const COMPLETED_AT = 'completed_at';
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

    /**
     * @return DateTime|null
     */
    public function getCompletedAt();

    /**
     * @param DateTime $dateTime
     * @return $this
     */
    public function setCompletedAt(DateTime $dateTime);
}
