<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\CouponInterface;

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
