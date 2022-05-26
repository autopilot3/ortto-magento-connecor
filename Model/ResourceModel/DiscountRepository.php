<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\ResourceModel;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Exception;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\ConditionInterface;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Api\Data\ConditionInterfaceFactory;
use Ortto\Connector\Api\Data\CouponResponseInterface;
use Ortto\Connector\Api\Data\PriceRuleResponseInterface;
use Ortto\Connector\Model\Data\CouponResponseFactory;
use Ortto\Connector\Model\Data\PriceRuleResponseFactory;
use Ortto\Connector\Api\Data\PriceRuleInterface;
use Ortto\Connector\Api\Data\SharedCouponInterface;
use Ortto\Connector\Api\DiscountRepositoryInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Magento\SalesRule\Api\Data\RuleInterfaceFactory;

class DiscountRepository implements DiscountRepositoryInterface
{
    const FOR_SHIPMENT_WITH_MATCHING_ITEMS = 2;

    private OrttoLoggerInterface $logger;
    private RuleRepositoryInterface $ruleRepository;
    private CouponRepositoryInterface $couponRepository;
    private RuleInterfaceFactory $rule;
    private ConditionInterfaceFactory $conditionFactory;
    private GroupRepositoryInterface $groupRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private CategoryRepositoryInterface $categoryRepository;
    private ProductRepositoryInterface $productRepository;
    private CouponFactory $couponFactory;
    private Data $helper;
    private CouponResponseFactory $couponResponseFactory;
    private PriceRuleResponseFactory $ruleResponseFactory;

    /**
     * @param OrttoLoggerInterface $logger
     * @param CouponRepositoryInterface $couponRepository
     * @param RuleRepositoryInterface $ruleRepository
     * @param RuleInterfaceFactory $rule
     * @param ConditionInterfaceFactory $conditionFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CouponFactory $couponFactory
     * @param CouponResponseFactory $couponResponseFactory
     * @param PriceRuleResponseFactory $ruleResponseFactory
     * @param Data $helper
     */
    public function __construct(
        OrttoLoggerInterface $logger,
        CouponRepositoryInterface $couponRepository,
        RuleRepositoryInterface $ruleRepository,
        RuleInterfaceFactory $rule,
        ConditionInterfaceFactory $conditionFactory,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        CouponFactory $couponFactory,
        CouponResponseFactory $couponResponseFactory,
        PriceRuleResponseFactory $ruleResponseFactory,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->ruleRepository = $ruleRepository;
        $this->couponRepository = $couponRepository;
        $this->rule = $rule;
        $this->conditionFactory = $conditionFactory;
        $this->helper = $helper;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->couponFactory = $couponFactory;
        $this->couponResponseFactory = $couponResponseFactory;
        $this->ruleResponseFactory = $ruleResponseFactory;
    }

    /**
     * @throws Exception
     */
    public function createPriceRule(PriceRuleInterface $rule): PriceRuleResponseInterface
    {
        $err = $rule->validate();
        if (!empty($err)) {
            throw $this->helper->newHTTPException($err, 400);
        }
        try {
            $newRule = $this->rule->create();
            $this->initialiseRule($newRule, $rule, false);
            $this->setCustomerGroups($newRule);
            $this->setConditions($newRule, $rule);
            $priceRule = $this->ruleRepository->save($newRule);
            $response = $this->ruleResponseFactory->create();
            $response->setId(To::int($priceRule->getRuleId()));
            return $response;
        } catch (\Exception $exception) {
            $this->logger->error($exception, "Failed to create new price rule");
            throw $this->helper->newHTTPException(sprintf('Internal Server Error: %s', $exception->getMessage()));
        }
    }

    /**
     * @throws Exception
     * @throws LocalizedException
     */
    public function updatePriceRule(PriceRuleInterface $rule): PriceRuleResponseInterface
    {
        try {
            $existing = $this->ruleRepository->getById($rule->getId());
            // This should not happen. Just in case
            if (empty($existing)) {
                throw $this->helper->newHTTPException(sprintf('Rule ID %d was not found', $rule->getId()), 404);
            }
            $this->initialiseRule($existing, $rule, true);
            $this->setConditions($existing, $rule);
            $priceRule = $this->ruleRepository->save($existing);
            $response = $this->ruleResponseFactory->create();
            $response->setId(To::int($priceRule->getRuleId()));
            return $response;
        } catch (NoSuchEntityException $e) {
            throw $this->helper->newHTTPException(sprintf('Rule ID %d was not found', $rule->getId()), 404);
        }
    }

    /**
     * @throws Exception
     */
    public function deletePriceRule(int $ruleId): void
    {
        try {
            $this->ruleRepository->deleteById($ruleId);
        } catch (NoSuchEntityException $e) {
            return;
        } catch (LocalizedException|\Exception $e) {
            $this->logger->error($e, sprintf("Failed to delete price rule ID %d", $ruleId));
            throw $this->helper->newHTTPException(sprintf('Internal Server Error: %s', $e->getMessage()));
        }
    }

    /**
     * @throws Exception
     * @throws LocalizedException
     */
    public function createCoupon(SharedCouponInterface $coupon): CouponResponseInterface
    {
        $err = $coupon->validate();
        if (!empty($err)) {
            throw $this->helper->newHTTPException($err, 400);
        }
        try {
            $rule = $this->ruleRepository->getById($coupon->getRuleId());
            // This should not happen. Just in case
            if (empty($rule)) {
                throw $this->helper->newHTTPException(sprintf('Rule ID %d was not found', $coupon->getRuleId()), 404);
            }
            if ($rule->getCouponType() === RuleInterface::COUPON_TYPE_NO_COUPON) {
                throw $this->helper->newHTTPException(
                    sprintf('Cannot add coupon to a rule with coupon type %s', $rule->getCouponType()),
                    400
                );
            }
        } catch (NoSuchEntityException $e) {
            throw $this->helper->newHTTPException(sprintf('Rule ID %d was not found', $coupon->getRuleId()), 404);
        }

        try {
            if (!$rule->getUseAutoGeneration()) {
                $this->searchCriteriaBuilder->addFilter(Coupon::KEY_IS_PRIMARY, true)
                    ->addFilter(Coupon::KEY_RULE_ID, $coupon->getRuleId());
                $primary = $this->couponRepository->getList($this->searchCriteriaBuilder->create())->getItems();
                if (!empty($primary)) {
                    // Only one coupon can be primary
                    // Update the code if needed and return the same coupon
                    foreach ($primary as $primaryCoupon) {
                        if ($primaryCoupon->getCode() !== $coupon->getCode()) {
                            $primaryCoupon->setCode($coupon->getCode());
                            $primaryCoupon->setUsagePerCustomer($rule->getUsesPerCustomer());
                            $primaryCoupon->setUsageLimit($rule->getUsesPerCoupon());
                            $this->couponRepository->save($primaryCoupon);
                        }
                        $response = $this->couponResponseFactory->create();
                        $response->setId(To::int($primaryCoupon->getCouponId()))
                            ->setCode((string)$primaryCoupon->getCode());
                        return $response;
                    }
                }
            }

            $now = $this->helper->nowUTC();
            $autoGenerate = $rule->getUseAutoGeneration();
            $newCoupon = $this->couponFactory->create();
            $newCoupon->setCode($coupon->getCode())
                ->setRuleId($coupon->getRuleId())
                ->setType($autoGenerate ? CouponInterface::TYPE_GENERATED : CouponInterface::TYPE_MANUAL)
                ->setIsPrimary(!$autoGenerate)
                ->setUsagePerCustomer($rule->getUsesPerCustomer())
                ->setCreatedAt($this->helper->toUTC($now))
                ->setUsageLimit($rule->getUsesPerCoupon());


            $created = $this->couponRepository->save($newCoupon);
            $response = $this->couponResponseFactory->create();
            $response->setId(To::int($created->getCouponId()))
                ->setCode((string)$created->getCode());
            return $response;
        } catch (AlreadyExistsException $e) {
            throw $this->helper->newHTTPException(sprintf('Duplicate coupon code %s', $coupon->getCode()), 400);
        } catch (\Exception $e) {
            $this->logger->error($e, "Failed to create new coupon");
            throw $this->helper->newHTTPException(sprintf('Internal Server Error: %s', $e->getMessage()));
        }
    }

    private function initialiseRule(RuleInterface $newRule, PriceRuleInterface $rule, bool $updateMode)
    {
        $newRule->setName($rule->getName())
            ->setUsesPerCoupon($rule->getTotalLimit())
            ->setUsesPerCustomer($rule->getPerCustomerLimit())
            // Will generate a unique coupon code per person when sending voucher email
            ->setUseAutoGeneration($rule->getIsUnique())
            ->setSortOrder($rule->getPriority())
            ->setIsRss($rule->getIsRss())
            ->setStopRulesProcessing($rule->getDiscardSubsequentRules())
            ->setApplyToShipping($rule->getApplyToShipping())
            ->setCouponType(RuleInterface::COUPON_TYPE_SPECIFIC_COUPON);

        $startDate = $rule->getStartDate();
        if (!empty($startDate)) {
            $from = $this->helper->toUTC($startDate);
            if ($from !== Config::EMPTY_DATE_TIME) {
                $newRule->setFromDate($from);
            }
        } else {
            if ($updateMode) {
                $newRule->setFromDate(null);
            }
        }

        $expiration = $rule->getExpirationDate();
        if (!empty($expiration)) {
            $to = $this->helper->toUTC($expiration);
            if ($to !== Config::EMPTY_DATE_TIME) {
                $newRule->setToDate($to);
            }
        } else {
            if ($updateMode) {
                $newRule->setToDate(null);
            }
        }

        $maxQuantity = $rule->getMaxQuantity();
        if (!empty($maxQuantity)) {
            $newRule->setDiscountQty(To::float($maxQuantity));
        }

        if (!$updateMode) {
            $newRule->setDescription($rule->getDescription())
                ->setIsActive(true)
                ->setIsAdvanced(true)
                ->setWebsiteIds([$rule->getWebsiteId()]);
        }

        $type = $rule->getType();
        switch ($type) {
            case PriceRuleInterface::TYPE_FIXED_AMOUNT:
                $newRule->setSimpleAction(RuleInterface::DISCOUNT_ACTION_FIXED_AMOUNT);
                $newRule->setDiscountAmount(To::float($rule->getValue()));
                break;
            case PriceRuleInterface::TYPE_FIXED_CART:
                $newRule->setSimpleAction(RuleInterface::DISCOUNT_ACTION_FIXED_AMOUNT_FOR_CART);
                $newRule->setDiscountAmount(To::float($rule->getValue()));
                break;
            case PriceRuleInterface::TYPE_PERCENTAGE:
                $newRule->setSimpleAction(RuleInterface::DISCOUNT_ACTION_BY_PERCENT);
                $newRule->setDiscountAmount(To::float($rule->getValue()));
                break;
            case PriceRuleInterface::TYPE_FREE_SHIPPING:
                // https://docs.magento.com/user-guide/marketing/price-rules-cart-free-shipping.html
                $newRule->setSimpleAction(RuleInterface::DISCOUNT_ACTION_BY_PERCENT);
                $newRule->setApplyToShipping(true);
                // NOTE: There is a bug in Magento. `salesrules.simple_free_shipping` column is numeric
                $newRule->setSimpleFreeShipping(self::FOR_SHIPMENT_WITH_MATCHING_ITEMS);
                break;
            case PriceRuleInterface::TYPE_BUY_X_GET_Y_FREE:
                $newRule->setSimpleAction(RuleInterface::DISCOUNT_ACTION_BUY_X_GET_Y);
                $newRule->setDiscountAmount(To::float($rule->getValue()));
                $step = $rule->getQuantityStep();
                if (!empty($step)) {
                    $newRule->setDiscountStep(To::int($step));
                }
        }
    }

    /**
     * @throws LocalizedException
     */
    private function setCustomerGroups(RuleInterface $newRule)
    {
        $search = $this->searchCriteriaBuilder->create();
        $groupIDs = [];
        $groups = $this->groupRepository->getList($search)->getItems();
        foreach ($groups as $group) {
            $groupIDs[] = $group->getId();
        }

        if (!empty($groupIDs)) {
            $newRule->setCustomerGroupIds($groupIDs);
        }
    }

    private function setConditions(RuleInterface $newRule, PriceRuleInterface $rule)
    {
        $rootCondition = $this->conditionFactory->create();
        $rootCondition->setConditionType('Magento\SalesRule\Model\Rule\Condition\Combine');
        $rootCondition->setAggregatorType(ConditionInterface::AGGREGATOR_TYPE_ALL);
        $rootCondition->setValue(true);

        /** @var ConditionInterface[] $conditions */
        $conditions = [];

        $min = $rule->getMinPurchaseAmount();
        if (!empty($min)) {
            $minTotalCondition = $this->conditionFactory->create();
            $minTotalCondition->setConditionType('Magento\SalesRule\Model\Rule\Condition\Address');
            $minTotalCondition->setAttributeName('base_subtotal');
            $minTotalCondition->setOperator(">=");
            $minTotalCondition->setValue(To::float($min));
            $conditions[] = $minTotalCondition;
        }

        $categories = $rule->getCategories();
        if (!empty($categories)) {
            $categoryIDs = [];
            foreach ($categories as $categoryId) {
                try {
                    if ($this->categoryRepository->get($categoryId)) {
                        $categoryIDs[] = $categoryId;
                    }
                } catch (NoSuchEntityException $e) {
                    $this->logger->warn('Product category was not found', ['category_id' => $categoryId]);
                    continue;
                }
            }
            if (!empty($categoryIDs)) {
                $productCondition = $this->conditionFactory->create();
                $productCondition->setConditionType('Magento\SalesRule\Model\Rule\Condition\Product');
                $productCondition->setAttributeName('category_ids');
                $productCondition->setValue($categoryIDs);
                $productCondition->setOperator("()");

                $condition = $this->conditionFactory->create();
                $condition->setValue(1);
                $condition->setConditionType('Magento\SalesRule\Model\Rule\Condition\Product\Found');
                $condition->setConditions([$productCondition]);
                $conditions[] = $condition;
            }
        }

        $productIDs = $rule->getProducts();
        if (!empty($productIDs)) {
            $productSKUs = [];
            $this->searchCriteriaBuilder->addFilter('entity_id', $productIDs, 'in');
            $products = $this->productRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            foreach ($products as $product) {
                $productSKUs[] = $product->getSku();
            }

            if (count($productSKUs) != count($productIDs)) {
                $this->logger->warn(
                    'Some products was not found for the price rule',
                    ['requested' => $productIDs, 'found' => $productSKUs]
                );
            }

            if (!empty($productSKUs)) {
                $productCondition = $this->conditionFactory->create();
                $productCondition->setConditionType('Magento\SalesRule\Model\Rule\Condition\Product');
                $productCondition->setAttributeName('sku');
                $productCondition->setValue($productSKUs);
                $productCondition->setOperator("()");

                $condition = $this->conditionFactory->create();
                $condition->setValue(1);
                $condition->setConditionType('Magento\SalesRule\Model\Rule\Condition\Product\Found');
                $condition->setConditions([$productCondition]);
                $condition->setAggregatorType(ConditionInterface::AGGREGATOR_TYPE_ALL);
                $conditions[] = $condition;
            }
        }

        if (!empty($conditions)) {
            $rootCondition->setConditions($conditions);
        }
        $newRule->setCondition($rootCondition);
    }
}
