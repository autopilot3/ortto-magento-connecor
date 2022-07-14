<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\PriceRuleResponseInterface;
use Ortto\Connector\Api\Data\PriceRuleInterface;
use Ortto\Connector\Api\Data\DiscountInterface;

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
     * @param DiscountInterface $discount
     * @return DiscountInterface
     */
    public function createDiscount(DiscountInterface $discount): DiscountInterface;

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param int $page
     * @param int $pageSize
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListPriceRuleResponseInterface
     */
    public function list(
        string $scopeType,
        int $scopeId,
        int $page,
        int $pageSize,
        array $data = []
    );
}
