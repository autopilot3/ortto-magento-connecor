<?php

namespace Autopilot\AP3Connector\API;

use Autopilot\AP3Connector\API\Data\CouponInterface;

/**
 *  Interface CouponRepositoryInterface
 * @api
 */
interface CouponRepositoryInterface
{
    /**
     * @param CouponInterface $coupon
     * @return void
     */
    public function create(CouponInterface $coupon);
}
