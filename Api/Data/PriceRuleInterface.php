<?php

namespace Ortto\Connector\Api\Data;

interface PriceRuleInterface extends SerializableInterface
{
    public const TYPE_PERCENTAGE = 'percentage';
    // A fix amount will be deducted from each individual item in the cart
    public const TYPE_FIXED_EACH_ITEM = 'fixed_each_item';
    // A fix amount will be deducted from the cart's total
    public const TYPE_FIXED_CART_TOTAL = 'fixed_cart_total';
    public const TYPE_FREE_SHIPPING = 'free_shipping';
    public const TYPE_BUY_X_GET_Y_FREE = 'buy_x_get_y_free';

    /**
     * String constants for property names
     */
    const NAME = "name";
    // Used for update only
    const ID = "id";
    const IS_UNIQUE = "is_unique";
    const TYPE = "type";
    const VALUE = "value";
    const RULE_CATEGORIES = "rule_categories";
    const RULE_PRODUCTS = "rule_products";
    const ACTION_CATEGORIES = "action_categories";
    const ACTION_PRODUCTS = "action_products";
    const EXPIRATION_DATE = "expiration_date";
    const START_DATE = "start_date";
    const TOTAL_LIMIT = "total_limit";
    const PER_CUSTOMER_LIMIT = "per_customer_limit";
    const WEBSITE_ID = 'website_id';
    const IS_RSS = "is_rss";
    const PRIORITY = "priority";
    const DISCARD_SUBSEQUENT_RULES = "discard_subsequent_rules";
    const MAX_QUANTITY = 'max_quantity';
    const MIN_QUANTITY = 'min_quantity';
    const MIN_PURCHASE_AMOUNT = 'min_purchase_amount';
    const BUY_X_QUANTITY = 'buy_x_quantity';
    const APPLY_TO_SHIPPING = "apply_to_shipping";
    const APPLY_FREE_SHIPPING_TO_MATCHING_ITEMS_ONLY = "apply_free_shipping_to_matching_items_only";
    const DESCRIPTION = "description";

    /**
     * Getter for Name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Setter for Name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void;

    /**
     * Getter for Id.
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Setter for Id.
     *
     * @param int $id
     *
     * @return void
     */
    public function setId(int $id): void;

    /**
     * Getter for Priority.
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Setter for Priority.
     *
     * @param int $priority
     *
     * @return void
     */
    public function setPriority(int $priority): void;

    /**
     * Getter for IsUnique.
     *
     * @return bool
     */
    public function getIsUnique(): bool;

    /**
     * Setter for IsUnique.
     *
     * @param bool $isUnique
     *
     * @return void
     */
    public function setIsUnique(bool $isUnique): void;

    /**
     * Getter for FreeShippingToMatchingItemsOnly.
     *
     * @return bool
     */
    public function getApplyFreeShippingToMatchingItemsOnly(): bool;

    /**
     * Setter for ApplyFreeShippingToMatchingItemsOnly.
     *
     * @param bool $applyFreeShippingToMatchingItemsOnly
     *
     * @return void
     */
    public function setApplyFreeShippingToMatchingItemsOnly(bool $applyFreeShippingToMatchingItemsOnly): void;


    /**
     * Getter for ApplyToShipping.
     *
     * @return bool
     */
    public function getApplyToShipping(): bool;

    /**
     * Setter for ApplyToShipping.
     *
     * @param bool $applyToShipping
     *
     * @return void
     */
    public function setApplyToShipping(bool $applyToShipping): void;

    /**
     * Getter for DiscardSubsequentRules.
     *
     * @return bool
     */
    public function getDiscardSubsequentRules(): bool;

    /**
     * Setter for DiscardSubsequentRules.
     *
     * @param bool $discardSubsequentRules
     *
     * @return void
     */
    public function setDiscardSubsequentRules(bool $discardSubsequentRules): void;

    /**
     * Getter for IsRss.
     *
     * @return bool
     */
    public function getIsRss(): bool;

    /**
     * Setter for IsRss.
     *
     * @param bool $isRss
     *
     * @return void
     */
    public function setIsRss(bool $isRss): void;

    /**
     * Getter for Type.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Setter for Type.
     *
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void;

    /**
     * Getter for Value.
     *
     * @return float
     */
    public function getValue(): float;

    /**
     * Setter for Value.
     *
     * @param float $value
     *
     * @return void
     */
    public function setValue(float $value): void;

    /**
     * Getter for MaxQuantity.
     *
     * @return float
     */
    public function getMaxQuantity(): float;

    /**
     * Setter for MaxQuantity.
     *
     * @param float|null $maxQuantity
     *
     * @return void
     */
    public function setMaxQuantity(float $maxQuantity): void;

    /**
     * Getter for MinQuantity.
     *
     * @return float
     */
    public function getMinQuantity(): float;

    /**
     * Setter for MinQuantity.
     *
     * @param float $minQuantity
     *
     * @return void
     */
    public function setMinQuantity(float $minQuantity): void;

    /**
     * Getter for MinPurchaseAmount.
     *
     * @return float
     */
    public function getMinPurchaseAmount(): float;

    /**
     * Setter for MinPurchaseAmount.
     *
     * @param float $minPurchaseAmount
     *
     * @return void
     */
    public function setMinPurchaseAmount(float $minPurchaseAmount): void;

    /**
     * Getter for BuyXQuantity.
     *
     * @return int|null
     */
    public function getBuyXQuantity(): int;

    /**
     * Setter for BuyXQuantity.
     *
     * @param int $buyXQuantity
     *
     * @return void
     */
    public function setBuyXQuantity(int $buyXQuantity): void;

    /**
     * Getter for RuleCategories.
     *
     * @return int[]|null
     */
    public function getRuleCategories(): ?array;

    /**
     * Setter for RuleCategories.
     *
     * @param int[]|null $ruleCategories
     *
     * @return void
     */
    public function setRuleCategories(?array $ruleCategories): void;

    /**
     * Getter for RuleProducts.
     *
     * @return int[]|null
     */
    public function getRuleProducts(): ?array;

    /**
     * Setter for RuleProducts.
     *
     * @param int[]|null $ruleProducts
     *
     * @return void
     */
    public function setRuleProducts(?array $ruleProducts): void;

    /**
     * Getter for ActionCategories.
     *
     * @return int[]|null
     */
    public function getActionCategories(): ?array;

    /**
     * Setter for ActionCategories.
     *
     * @param int[] $actionCategories
     *
     * @return void
     */
    public function setActionCategories(?array $actionCategories): void;

    /**
     * Getter for ActionProducts.
     *
     * @return int[]|null
     */
    public function getActionProducts(): ?array;

    /**
     * Setter for ActionProducts.
     *
     * @param int[]|null $actionProducts
     *
     * @return void
     */
    public function setActionProducts(?array $actionProducts): void;

    /**
     * Getter for ExpirationDate.
     *
     * @return string|null
     */
    public function getExpirationDate(): ?string;

    /**
     * Setter for ExpirationDate.
     *
     * @param string|null $expirationDate
     *
     * @return void
     */
    public function setExpirationDate(?string $expirationDate): void;

    /**
     * Getter for StartDate.
     *
     * @return string|null
     */
    public function getStartDate(): ?string;

    /**
     * Setter for StartDate.
     *
     * @param string|null $startDate
     *
     * @return void
     */
    public function setStartDate(?string $startDate): void;

    /**
     * Getter for TotalLimit.
     *
     * @return int
     */
    public function getTotalLimit(): int;

    /**
     * Setter for TotalLimit.
     *
     * @param int $totalLimit
     *
     * @return void
     */
    public function setTotalLimit(int $totalLimit): void;

    /**
     * Getter for PerCustomerLimit.
     *
     * @return int
     */
    public function getPerCustomerLimit(): int;

    /**
     * Setter for PerCustomerLimit.
     *
     * @param int $perCustomerLimit
     *
     * @return void
     */
    public function setPerCustomerLimit(int $perCustomerLimit): void;

    /**
     * @param int $websiteId
     * @return void
     */
    public function setWebsiteId(int $websiteId): void;

    /**
     * @return int
     */
    public function getWebsiteId(): int;

    /**
     * @return string
     */
    public function validate(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $description
     * @return void
     */
    public function setDescription(string $description);
}
