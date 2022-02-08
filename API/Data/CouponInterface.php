<?php

namespace Autopilot\AP3Connector\API\Data;

/**
 * Interface CouponInterface
 * @api
 */
interface CouponInterface
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
