<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api\Data;

/**
 * Interface CouponInterface
 * @api
 */
interface CouponInterface extends SerializableInterface
{
    const DATA_TITLE = 'title';

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): CouponInterface;
}
