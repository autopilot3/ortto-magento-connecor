<?php

namespace Ortto\Connector\Api\Data;

interface PriceRuleInterface extends SerializableInterface
{
    public const TYPE_PERCENTAGE = "percentage";
    public const TYPE_FIXED_AMOUNT = "fixed";
    public const TYPE_FREE_SHIPPING = "free_shipping";

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
