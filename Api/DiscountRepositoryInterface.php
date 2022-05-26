<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\CouponResponseInterface;
use Ortto\Connector\Api\Data\PriceRuleResponseInterface;
use Ortto\Connector\Api\Data\PriceRuleInterface;
use Ortto\Connector\Api\Data\SharedCouponInterface;

/**
 *  Interface DiscountRepositoryInterface
 * @api
 */
interface DiscountRepositoryInterface
{
    /**
     * @param PriceRuleInterface $rule
     * @return PriceRuleResponseInterface
     */
    public function createPriceRule(PriceRuleInterface $rule): PriceRuleResponseInterface;

    /**
     * @param PriceRuleInterface $rule
     * @return PriceRuleResponseInterface
     */
    public function updatePriceRule(PriceRuleInterface $rule): PriceRuleResponseInterface;

    /**
     * @param int $ruleId
     * @return void
     */
    public function deletePriceRule(int $ruleId): void;

    /**
     * @param SharedCouponInterface $coupon
     * @return CouponResponseInterface
     */
    public function createCoupon(SharedCouponInterface $coupon): CouponResponseInterface;
}
