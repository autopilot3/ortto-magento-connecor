<?php

namespace Ortto\Connector\Api\Data;

interface PriceRuleInterface extends SerializableInterface
{
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED_AMOUNT = 'fixed';
    public const TYPE_FIXED_CART = 'fixed_cart';
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
    const CATEGORIES = "categories";
    const PRODUCTS = "products";
    const EXPIRATION_DATE = "expiration_date";
    const TOTAL_LIMIT = "total_limit";
    const PER_CUSTOMER_LIMIT = "per_customer_limit";
    const WEBSITE_ID = 'website_id';
    const MIN_PURCHASE_AMOUNT = 'min_purchase_amount';
    const IS_RSS = "is_rss";
    const PRIORITY = "priority";
    const DISCARD_SUBSEQUENT_RULES = "discard_subsequent_rules";
    const MAX_QUANTITY = 'max_quantity';
    const QUANTITY_STEP = 'quantity_step';
    const APPLY_TO_SHIPPING = "apply_to_shipping";

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
     * @return $this
     */
    public function setId(int $id);

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
     * @return $this
     */
    public function setPriority(int $priority);

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
     * @return float|null
     */
    public function getValue(): ?float;

    /**
     * Setter for Value.
     *
     * @param float|null $value
     *
     * @return void
     */
    public function setValue(?float $value): void;

    /**
     * Getter for MaxQuantity.
     *
     * @return float|null
     */
    public function getMaxQuantity(): ?float;

    /**
     * Setter for MaxQuantity.
     *
     * @param float|null $maxQuantity
     *
     * @return void
     */
    public function setMaxQuantity(?float $maxQuantity): void;

    /**
     * Getter for QuantityStep.
     *
     * @return int|null
     */
    public function getQuantityStep(): ?int;

    /**
     * Setter for QuantityStep.
     *
     * @param int|null $quantityStep
     *
     * @return void
     */
    public function setQuantityStep(?int $quantityStep): void;

    /**
     * Getter for MinPurchaseAmount.
     *
     * @return float|null
     */
    public function getMinPurchaseAmount(): ?float;

    /**
     * Setter for MinPurchaseAmount.
     *
     * @param float|null $minPurchaseAmount
     *
     * @return void
     */
    public function setMinPurchaseAmount(?float $minPurchaseAmount): void;

    /**
     * Getter for Categories.
     *
     * @return int[]|null
     */
    public function getCategories(): ?array;

    /**
     * Setter for Categories.
     *
     * @param int[] $categories
     *
     * @return void
     */
    public function setCategories(array $categories): void;

    /**
     * Getter for Products.
     *
     * @return int[]|null
     */
    public function getProducts(): ?array;

    /**
     * Setter for Products.
     *
     * @param int[] $products
     *
     * @return void
     */
    public function setProducts(array $products): void;

    /**
     * Getter for ExpirationDate.
     *
     * @return string|null
     */
    public function getExpirationDate(): string;

    /**
     * Setter for ExpirationDate.
     *
     * @param string|null $expirationDate
     *
     * @return void
     */
    public function setExpirationDate(string $expirationDate): void;

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
}
