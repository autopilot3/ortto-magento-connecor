<?php
declare(strict_types=1);

namespace Ortto\Connector\REST;

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
use Magento\SalesRule\Api\Data\ConditionInterfaceFactory;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\Data\RuleInterfaceFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\CouponFactory;
use Ortto\Connector\Api\Data\DiscountInterface;
use Ortto\Connector\Api\Data\PriceRuleInterface;
use Ortto\Connector\Api\Data\PriceRuleResponseInterface;
use Ortto\Connector\Api\DiscountRepositoryInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Model\Data\DiscountFactory;
use Ortto\Connector\Model\Data\ListPriceRuleResponseFactory;
use Ortto\Connector\Model\Data\PriceRuleFactory;
use Ortto\Connector\Model\Data\PriceRuleResponseFactory;

class DiscountApi extends RestApiBase implements DiscountRepositoryInterface
{

    private const RULE_TYPE_COMBINE = 'Magento\SalesRule\Model\Rule\Condition\Combine';
    private const RULE_TYPE_ADDRESS = 'Magento\SalesRule\Model\Rule\Condition\Address';
    private const RULE_TYPE_PRODUCT = 'Magento\SalesRule\Model\Rule\Condition\Product';
    private const RULE_TYPE_PRODUCT_FOUND = 'Magento\SalesRule\Model\Rule\Condition\Product\Found';

    private const OP_GT_OR_EQ = '>=';
    private const OP_GT = '>';
    private const OP_IN = '()';

    const NO_FREE_SHIPPING = 0;
    const APPLY_FREE_SHIPPING_TO_MATCHING_ITEMS_ONLY = 1;
    const APPLY_FREE_SHIPPING_TO_CART_WITH_MATCHING_ITEMS = 2;

    private const MIN_PURCHASE_AMOUNT = 'min_purchase_amount';
    private const MIN_QUANTITY = 'min_quantity';
    private const PRODUCTS = 'products';
    private const CATEGORIES = 'categories';

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
    private DiscountFactory $discountFactory;
    private PriceRuleResponseFactory $ruleResponseFactory;
    private PriceRuleFactory $priceRuleFactory;
    private ListPriceRuleResponseFactory $listPriceRuleResponseFactory;

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
     * @param DiscountFactory $discountFactory
     * @param PriceRuleResponseFactory $ruleResponseFactory
     * @param Data $helper
     * @param PriceRuleFactory $priceRuleFactory
     * @param ListPriceRuleResponseFactory $listPriceRuleResponseFactory
     * @param ScopeManagerInterface $scopeManager
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
        DiscountFactory $discountFactory,
        PriceRuleResponseFactory $ruleResponseFactory,
        Data $helper,
        \Ortto\Connector\Model\Data\PriceRuleFactory $priceRuleFactory,
        ListPriceRuleResponseFactory $listPriceRuleResponseFactory,
        ScopeManagerInterface $scopeManager
    ) {
        parent::__construct($scopeManager);
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
        $this->discountFactory = $discountFactory;
        $this->ruleResponseFactory = $ruleResponseFactory;
        $this->priceRuleFactory = $priceRuleFactory;
        $this->listPriceRuleResponseFactory = $listPriceRuleResponseFactory;
    }

    /** @inheirtDoc
     * @throws LocalizedException
     */
    public function list(
        string $scopeType,
        int $scopeId,
        int $page,
        int $pageSize,
        array $data = []
    ) {
        $scope = $this->validateScope($scopeType, $scopeId);

        // TODO: Filter by website ID
        $search = $this->searchCriteriaBuilder
            ->setPageSize($pageSize)
            ->setCurrentPage($page)
            ->addFilter('is_active', 1)
            ->addFilter('coupon_type', 2)
            ->addFilter('simple_action', [
                RuleInterface::DISCOUNT_ACTION_FIXED_AMOUNT,
                RuleInterface::DISCOUNT_ACTION_FIXED_AMOUNT_FOR_CART,
                RuleInterface::DISCOUNT_ACTION_BY_PERCENT,
                RuleInterface::DISCOUNT_ACTION_BUY_X_GET_Y,
            ], 'in');

        $collection = $this->ruleRepository->getList($search->create());
        $total = To::int($collection->getTotalCount());
        $result = $this->listPriceRuleResponseFactory->create();
        $result->setTotal($total);
        if ($total == 0) {
            return $result;
        }

        $rules = [];
        // TODO: Optimize loading product IDs from SKUs
        foreach ($collection->getItems() as $rule) {
            if ($priceRule = $this->convertRule($rule, $scope->getWebsiteId())) {
                $rules[] = $priceRule;
            };
        }
        $result->setItems($rules);
        $result->setHasMore($page < $total / $pageSize);
        return $result;
    }

    /** @inheirtDoc
     * @throws \Exception
     */
    public function getById(
        string $scopeType,
        int $scopeId,
        int $ruleId
    ) {
        try {
            $scope = $this->validateScope($scopeType, $scopeId);
            $rule = $this->ruleRepository->getById($ruleId);
        } catch (NoSuchEntityException $e) {
            throw $this->notFoundError();
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw $this->httpError($e->getMessage());
        }
        if (empty($rule)) {
            throw $this->notFoundError();
        }
        return $this->convertRule($rule, $scope->getWebsiteId());
    }

    /**
     * @param RuleInterface $rule
     * @param int $websiteId
     * @return PriceRuleInterface|bool
     */
    private function convertRule(RuleInterface $rule, int $websiteId)
    {
        $data = $this->priceRuleFactory->create();
        $data->setId(To::int($rule->getRuleId()));
        $data->setName((string)$rule->getName());
        $data->setIsUnique(To::bool($rule->getUseAutoGeneration()));
        $data->setDescription((string)$rule->getDescription());
        $data->setIsRSS(To::bool($rule->getIsRss()));
        $data->setPriority(To::int($rule->getSortOrder()));
        $data->setTotalLimit(To::int($rule->getUsesPerCoupon()));
        $data->setPerCustomerLimit(To::int($rule->getUsesPerCustomer()));
        $data->setDiscardSubsequentRules(To::bool($rule->getStopRulesProcessing()));
        $applyToShipping = To::bool($rule->getApplyToShipping());
        $data->setApplyToShipping($applyToShipping);
        $data->setMaxQuantity(To::int($rule->getDiscountQty()));
        $data->setValue(To::float($rule->getDiscountAmount()));
        $data->setStartDate($rule->getFromDate());
        $data->setExpirationDate($rule->getToDate());
        $data->setWebsiteId($websiteId);
        switch ($rule->getSimpleAction()) {
            case RuleInterface::DISCOUNT_ACTION_FIXED_AMOUNT:
                $data->setType(PriceRuleInterface::TYPE_FIXED_EACH_ITEM);
                break;
            case RuleInterface::DISCOUNT_ACTION_FIXED_AMOUNT_FOR_CART:
                $data->setType(PriceRuleInterface::TYPE_FIXED_CART_TOTAL);
                break;
            case RuleInterface::DISCOUNT_ACTION_BY_PERCENT:
                if ($applyToShipping) {
                    $data->setType(PriceRuleInterface::TYPE_FREE_SHIPPING);
                    $simpleFreeShipping = (string)$rule->getSimpleFreeShipping();
                    $toMatchingItems = ($simpleFreeShipping == self::APPLY_FREE_SHIPPING_TO_MATCHING_ITEMS_ONLY);
                    $data->setApplyFreeShippingToMatchingItemsOnly($toMatchingItems);
                    $data->setValue(0);
                } else {
                    $data->setType(PriceRuleInterface::TYPE_PERCENTAGE);
                }
                break;
            case RuleInterface::DISCOUNT_ACTION_BUY_X_GET_Y:
                $data->setType(PriceRuleInterface::TYPE_BUY_X_GET_Y_FREE);
                $data->setApplyToShipping(false);
                $data->setBuyXQuantity(To::int($rule->getDiscountStep()));
                break;
            default:
                $this->logger->warn("Invalid price rule type", ['simple_action' => $rule->getSimpleAction()]);
                return false;
        }

        $ruleConditions = $this->processCondition($rule->getCondition());
        if (array_key_exists(self::MIN_QUANTITY, $ruleConditions)) {
            $data->setMinQuantity($ruleConditions[self::MIN_QUANTITY]);
        }
        if (array_key_exists(self::MIN_PURCHASE_AMOUNT, $ruleConditions)) {
            $data->setMinPurchaseAmount($ruleConditions[self::MIN_PURCHASE_AMOUNT]);
        }
        if (array_key_exists(self::PRODUCTS, $ruleConditions)) {
            $data->setRuleProducts(array_unique($ruleConditions[self::PRODUCTS]));
        }
        if (array_key_exists(self::CATEGORIES, $ruleConditions)) {
            $data->setRuleCategories(array_unique($ruleConditions[self::CATEGORIES]));
        }

        $ruleConditions = $this->processCondition($rule->getActionCondition());
        if (array_key_exists(self::PRODUCTS, $ruleConditions)) {
            $data->setActionProducts(array_unique($ruleConditions[self::PRODUCTS]));
        }
        if (array_key_exists(self::CATEGORIES, $ruleConditions)) {
            $data->setActionCategories(array_unique($ruleConditions[self::CATEGORIES]));
        }

        return $data;
    }


    private function processCondition(ConditionInterface $condition): array
    {
        $data = [];
        switch ($condition->getConditionType()) {
            case 'Magento\SalesRule\Model\Rule\Condition\Combine':
            case 'Magento\SalesRule\Model\Rule\Condition\Product\Found':
                if ($subConditions = $condition->getConditions()) {
                    foreach ($subConditions as $subCondition) {
                        $subData = $this->processCondition($subCondition);
                        if (!empty($subData)) {
                            foreach ($subData as $key => $value) {
                                if (is_array($value)) {
                                    foreach ($value as $v) {
                                        $data[$key][] = $v;
                                    }
                                } else {
                                    $data[$key] = $value;
                                }
                            }
                        }
                    }
                }
                break;
            case 'Magento\SalesRule\Model\Rule\Condition\Address':
                switch ($condition->getAttributeName()) {
                    case 'base_subtotal':
                        if ($condition->getOperator() == '>=') {
                            $data[self::MIN_PURCHASE_AMOUNT] = To::float($condition->getValue());
                        }
                        break;
                    case 'total_qty':
                        if ($condition->getOperator() == '>=') {
                            $data[self::MIN_QUANTITY] = To::float($condition->getValue());
                        }
                        break;
                }
                break;
            case 'Magento\SalesRule\Model\Rule\Condition\Product':
                switch ($condition->getAttributeName()) {
                    case 'category_ids':
                        $entityIds = $this->getCategoryIdsFromCondition($condition);
                        foreach ($entityIds as $id) {
                            $data[self::CATEGORIES][] = $id;
                        }
                        break;
                    case 'sku':
                        $entityIds = $this->getProductIdsFromCondition($condition);
                        foreach ($entityIds as $id) {
                            $data[self::PRODUCTS][] = $id;
                        }
                        break;
                }
                break;
        }
        return $data;
    }

    private function getCategoryIdsFromCondition(ConditionInterface $condition): array
    {
        $operator = $condition->getOperator();
        if ($operator == '()' || $operator == '==') {
            return $this->getIntegerList($condition->getValue());
        }
        return [];
    }

    private function getProductIdsFromCondition(ConditionInterface $condition): array
    {
        $skus = [];
        $operator = $condition->getOperator();
        if ($operator == '()' || $operator == '==') {
            $skus = $this->getStringList($condition->getValue());
        }

        $entityIds = [];
        if (!empty($skus)) {
            $skus = array_unique($skus);
            $this->searchCriteriaBuilder->addFilter('sku', $skus, 'in');
            $products = $this->productRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            foreach ($products as $product) {
                $entityIds[] = To::int($product->getId());
            }
        }

        return $entityIds;
    }

    private function getStringList($value): array
    {
        if (is_string($value)) {
            if (empty($value)) {
                return [];
            }
            return [$value];
        }
        $result = [];
        if (is_array($value)) {
            foreach ($value as $v) {
                if ($item = (string)$v) {
                    $result[] = $item;
                }
            }
            return $result;
        }
        return [];
    }

    private function getIntegerList($value): array
    {
        if (is_string($value)) {
            $v = To::int($value);
            if ($v > 0) {
                return [$v];
            }
            return [];
        }
        if (is_int($value)) {
            return [$value];
        }
        $result = [];
        if (is_array($value)) {
            foreach ($value as $v) {
                if (is_int($v) && $v > 0) {
                    $result[] = $v;
                    continue;
                }
                if (is_string($v)) {
                    $v = To::int($value);
                    if ($v > 0) {
                        $result[] = $v;
                    }
                }
            }
            return $result;
        }
        return [];
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
            $this->setConditions($newRule, $rule, false);
            $this->setCustomerGroups($newRule);
            $priceRule = $this->ruleRepository->save($newRule);
            $response = $this->ruleResponseFactory->create();
            $ruleId = To::int($priceRule->getRuleId());
            $response->setId($ruleId);
            if (!$rule->getIsUnique() && $rule->getCode() != '') {
                $discount = $this->discountFactory->create();
                $discount->setRuleId($ruleId);
                $discount->setCode($rule->getCode());
                $this->upsertDiscount($discount);
            }
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
        $err = $rule->validate();
        if (!empty($err)) {
            throw $this->helper->newHTTPException($err, 400);
        }
        try {
            $existing = $this->ruleRepository->getById($rule->getId());
            // This should not happen. Just in case
            if (empty($existing)) {
                throw $this->helper->newHTTPException(sprintf('Rule ID %d was not found', $rule->getId()), 404);
            }
            $this->initialiseRule($existing, $rule, true);
            $this->setConditions($existing, $rule, true);
            $priceRule = $this->ruleRepository->save($existing);
            $response = $this->ruleResponseFactory->create();
            $ruleId = To::int($priceRule->getRuleId());
            $response->setId($ruleId);
            if (!$rule->getIsUnique() && $rule->getCode() != '') {
                $discount = $this->discountFactory->create();
                $discount->setRuleId($ruleId);
                $discount->setCode($rule->getCode());
                $this->upsertDiscount($discount);
            }
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
    public function upsertDiscount(DiscountInterface $discount): DiscountInterface
    {
        $code = $discount->getCode();
        if (empty($code)) {
            throw $this->helper->newHTTPException('Discount code cannot be empty', 400);
        }
        $err = $discount->validate();
        if (!empty($err)) {
            throw $this->helper->newHTTPException($err, 400);
        }
        try {
            $rule = $this->ruleRepository->getById($discount->getRuleId());
            // This should not happen. Just in case
            if (empty($rule)) {
                throw $this->helper->newHTTPException(sprintf('Rule ID %d was not found', $discount->getRuleId()), 404);
            }
            if ($rule->getCouponType() === RuleInterface::COUPON_TYPE_NO_COUPON) {
                throw $this->helper->newHTTPException(
                    sprintf('Cannot add coupon to a rule with coupon type %s', $rule->getCouponType()),
                    400
                );
            }
        } catch (NoSuchEntityException $e) {
            throw $this->helper->newHTTPException(sprintf('Rule ID %d was not found', $discount->getRuleId()), 404);
        }

        $ruleID = To::int($rule->getRuleId());

        try {
            // Auto generate (aka Unique) coupon. Unique code is provided by Ortto.
            if ($rule->getUseAutoGeneration()) {
                $response = $this->discountFactory->create();
                $response->setRuleId($ruleID);
                $response->setCode($code);
                $this->searchCriteriaBuilder
                    ->addFilter(Coupon::KEY_CODE, $code)
                    ->addFilter(Coupon::KEY_RULE_ID, $ruleID);
                $coupons = $this->couponRepository->getList($this->searchCriteriaBuilder->create())->getItems();
                if (!empty($coupons)) {
                    return $response;
                }

                $now = $this->helper->nowUTC();
                $newCoupon = $this->couponFactory->create();
                $newCoupon->setCode($code)
                    ->setRuleId($ruleID)
                    ->setType(CouponInterface::TYPE_GENERATED)
                    ->setIsPrimary(false)
                    ->setUsagePerCustomer($rule->getUsesPerCustomer())
                    ->setCreatedAt($this->helper->toUTC($now))
                    ->setUsageLimit($rule->getUsesPerCoupon());
                $this->couponRepository->save($newCoupon);
                return $response;
            }

            // Shared coupon

            $this->searchCriteriaBuilder->addFilter(Coupon::KEY_IS_PRIMARY, true)
                ->addFilter(Coupon::KEY_RULE_ID, $ruleID);
            $primary = $this->couponRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            if (!empty($primary)) {
                // Only one coupon can be primary
                // Update the code if needed and return the same coupon
                foreach ($primary as $primaryCoupon) {
                    if ($primaryCoupon->getCode() !== $discount->getCode()) {
                        $primaryCoupon->setCode($discount->getCode());
                        $primaryCoupon->setUsagePerCustomer($rule->getUsesPerCustomer());
                        $primaryCoupon->setUsageLimit($rule->getUsesPerCoupon());
                        $this->couponRepository->save($primaryCoupon);
                    }
                    $response = $this->discountFactory->create();
                    $response->setRuleId($ruleID);
                    $response->setCode((string)$primaryCoupon->getCode());
                    return $response;
                }
            }

            $now = $this->helper->nowUTC();
            $newCoupon = $this->couponFactory->create();
            $newCoupon->setCode($discount->getCode())
                ->setRuleId($ruleID)
                ->setType(CouponInterface::TYPE_MANUAL)
                ->setIsPrimary(true)
                ->setUsagePerCustomer($rule->getUsesPerCustomer())
                ->setCreatedAt($this->helper->toUTC($now))
                ->setUsageLimit($rule->getUsesPerCoupon());
            $created = $this->couponRepository->save($newCoupon);
            $response = $this->discountFactory->create();
            $response->setRuleId($ruleID);
            $response->setCode((string)$created->getCode());
            return $response;
        } catch (AlreadyExistsException $e) {
            $this->logger->error($e, "Duplicate coupon code");
            throw $this->helper->newHTTPException(sprintf('Duplicate coupon code %s', $discount->getCode()), 409);
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
            ->setDiscountQty($rule->getMaxQuantity())
            ->setDiscountAmount($rule->getValue())
            ->setSimpleFreeShipping(self::NO_FREE_SHIPPING)
            ->setCouponType(RuleInterface::COUPON_TYPE_SPECIFIC_COUPON)
            ->setFromDate($rule->getStartDate())
            ->setDiscountStep(0)
            ->setToDate($rule->getExpirationDate());

        if (!$updateMode) {
            $newRule->setDescription($rule->getDescription())
                ->setIsActive(true)
                ->setIsAdvanced(true)
                ->setWebsiteIds([$rule->getWebsiteId()]);
        }

        $type = $rule->getType();
        switch ($type) {
            case PriceRuleInterface::TYPE_FIXED_EACH_ITEM:
                $newRule->setSimpleAction(RuleInterface::DISCOUNT_ACTION_FIXED_AMOUNT);
                break;
            case PriceRuleInterface::TYPE_FIXED_CART_TOTAL:
                $newRule->setSimpleAction(RuleInterface::DISCOUNT_ACTION_FIXED_AMOUNT_FOR_CART);
                break;
            case PriceRuleInterface::TYPE_PERCENTAGE:
                $newRule->setSimpleAction(RuleInterface::DISCOUNT_ACTION_BY_PERCENT);
                break;
            case PriceRuleInterface::TYPE_FREE_SHIPPING:
                // https://docs.magento.com/user-guide/marketing/price-rules-cart-free-shipping.html
                $newRule->setSimpleAction(RuleInterface::DISCOUNT_ACTION_BY_PERCENT);
                $newRule->setApplyToShipping(true);
                // NOTE: There is a bug in Magento. `salesrules.simple_free_shipping` column is numeric
                if ($rule->getApplyFreeShippingToMatchingItemsOnly()) {
                    $newRule->setSimpleFreeShipping(self::APPLY_FREE_SHIPPING_TO_MATCHING_ITEMS_ONLY);
                } else {
                    $newRule->setSimpleFreeShipping(self::APPLY_FREE_SHIPPING_TO_CART_WITH_MATCHING_ITEMS);
                }
                $newRule->setDiscountAmount(0);
                $newRule->setDiscountQty(0);
                break;
            case PriceRuleInterface::TYPE_BUY_X_GET_Y_FREE:
                $newRule->setApplyToShipping(false);
                $newRule->setSimpleAction(RuleInterface::DISCOUNT_ACTION_BUY_X_GET_Y);
                // DiscountAmount (Rule value) = Y
                // DiscountStep = X
                $newRule->setDiscountStep($rule->getBuyXQuantity());
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

    private function setConditions(RuleInterface $newRule, PriceRuleInterface $rule, bool $updateMode)
    {
        /** @var ConditionInterface[] $ruleConditions */
        $ruleConditions = [];
        /** @var ConditionInterface[] $actionConditions */
        $actionConditions = [];

        $minAmount = $rule->getMinPurchaseAmount();
        if ($minAmount > 0) {
            $ruleConditions[] = $this->buildCondition(
                self::RULE_TYPE_ADDRESS,
                $minAmount,
                'base_subtotal',
                self::OP_GT_OR_EQ
            );
        }

        $minQuantity = $rule->getMinQuantity();
        if ($minQuantity > 0) {
            $ruleConditions[] = $this->buildCondition(
                self::RULE_TYPE_ADDRESS,
                $minQuantity,
                'total_qty',
                // With Buy X get Y, we must go with Greater Than operand. For some reason Magento
                // applies the cart rule to shipping, when the number of ordered items is less than X
                // (in which no discount should be applied at all)
                $rule->getType() == PriceRuleInterface::TYPE_BUY_X_GET_Y_FREE ? self::OP_GT : self::OP_GT_OR_EQ
            );
        }

        $copyRuleProducts = true;
        if ($rule->getType() == PriceRuleInterface::TYPE_FREE_SHIPPING) {
            $copyRuleProducts = $rule->getApplyFreeShippingToMatchingItemsOnly();
        }

        $actionCategoryIDs = [];
        $actionProductIDs = [];

        // Categories and Product rules are mutually exclusive (See $rule->validate())
        $ruleCategoryIDs = $rule->getRuleCategories();
        if (!empty($ruleCategoryIDs)) {
            $categoriesCondition = $this->getCategoriesCondition($ruleCategoryIDs);
            if ($categoriesCondition != null) {
                $condition = $this->buildCondition(
                    self::RULE_TYPE_PRODUCT_FOUND,
                    1
                )->setConditions([$categoriesCondition]);
                $ruleConditions[] = $condition;
                if ($copyRuleProducts) {
                    $actionCategoryIDs = $ruleCategoryIDs;
                }
            }
        }

        $ruleProductIDs = $rule->getRuleProducts();
        if (!empty($ruleProductIDs)) {
            $productsCondition = $this->getProductConditions($ruleProductIDs);
            if ($productsCondition != null) {
                $condition = $this->buildCondition(self::RULE_TYPE_PRODUCT_FOUND, 1);
                $condition->setConditions([$productsCondition])
                    ->setAggregatorType(ConditionInterface::AGGREGATOR_TYPE_ALL);
                $ruleConditions[] = $condition;
                if ($copyRuleProducts) {
                    $actionProductIDs = $ruleProductIDs;
                }
            }
        }

        // Categories and Product rules are mutually exclusive (See $rule->validate())
        $actionCategories = array_merge($rule->getActionCategories() ?? [], $actionCategoryIDs);
        if (!empty($actionCategories)) {
            $condition = $this->getCategoriesCondition(array_unique($actionCategories));
            if (!empty($condition)) {
                $actionConditions[] = $condition;
            }
        }

        $actionProducts = array_merge($rule->getActionProducts() ?? [], $actionProductIDs);
        if (!empty($actionProducts)) {
            $condition = $this->getProductConditions(array_unique($actionProducts));
            if (!empty($condition)) {
                $actionConditions[] = $condition;
            }
        }

        if (!empty($ruleConditions)) {
            $newRule->setCondition($this->buildConditionsGroup($ruleConditions));
        } else {
            if ($updateMode) {
                $newRule->setCondition($this->buildConditionsGroup());
            }
        }

        if (!empty($actionConditions)) {
            $newRule->setActionCondition($this->buildConditionsGroup($actionConditions));
        } else {
            if ($updateMode) {
                $newRule->setActionCondition($this->buildConditionsGroup());
            }
        }
    }

    private function buildCondition(
        string $type,
        $value,
        string $attribute = '',
        string $operator = ''
    ): ConditionInterface {
        $condition = $this->conditionFactory->create();
        $condition->setConditionType($type);
        $condition->setAttributeName($attribute);
        $condition->setOperator($operator);
        $condition->setValue($value);
        return $condition;
    }

    private function buildConditionsGroup(array $children = null): ConditionInterface
    {
        $condition = $this->conditionFactory->create();
        $condition->setConditionType(self::RULE_TYPE_COMBINE);
        $condition->setAggregatorType(ConditionInterface::AGGREGATOR_TYPE_ALL);
        $condition->setValue(true);
        $condition->setConditions($children);
        return $condition;
    }

    /**
     * @param int[] $categoryIDs
     * @return ConditionInterface|null
     */
    private function getCategoriesCondition(array $categoryIDs): ?ConditionInterface
    {
        $validIDs = [];
        foreach ($categoryIDs as $categoryId) {
            try {
                if ($this->categoryRepository->get($categoryId)) {
                    $validIDs[] = $categoryId;
                }
            } catch (NoSuchEntityException $e) {
                $this->logger->warn('Product category was not found', ['category_id' => $categoryId]);
                continue;
            }
        }
        if (empty($validIDs)) {
            return null;
        }
        return $this->buildCondition(self::RULE_TYPE_PRODUCT, $validIDs, 'category_ids', self::OP_IN);
    }

    /**
     * @param int[] $productIDs
     * @return ConditionInterface|null
     */
    private function getProductConditions(array $productIDs): ?ConditionInterface
    {
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

        if (empty($productSKUs)) {
            return null;
        }

        return $this->buildCondition(self::RULE_TYPE_PRODUCT, $productSKUs, 'sku', self::OP_IN);
    }
}
