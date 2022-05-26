<?php

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\PriceRuleInterface;
use Ortto\Connector\Helper\To;

class PriceRule extends DataObject implements PriceRuleInterface
{
    /** @inerhitDoc */
    public function getName(): string
    {
        return (string)$this->getData(self::NAME);
    }

    /** @inerhitDoc */
    public function setName(string $name): void
    {
        $this->setData(self::NAME, $name);
    }

    /** @inerhitDoc */
    public function getId(): int
    {
        return To::int($this->getData(self::ID));
    }

    /** @inerhitDoc */
    public function setId(int $id)
    {
        $this->setData(self::ID, $id);
        return $this;
    }

    /** @inerhitDoc */
    public function getIsUnique(): bool
    {
        return To::bool($this->getData(self::IS_UNIQUE));
    }

    /** @inerhitDoc */
    public function setIsUnique(bool $isUnique): void
    {
        $this->setData(self::IS_UNIQUE, $isUnique);
    }

    /** @inerhitDoc */
    public function getApplyToShipping(): bool
    {
        return To::bool($this->getData(self::APPLY_TO_SHIPPING));
    }

    /** @inerhitDoc */
    public function setApplyToShipping(bool $applyToShipping): void
    {
        $this->setData(self::APPLY_TO_SHIPPING, $applyToShipping);
    }

    /** @inerhitDoc */
    public function getDiscardSubsequentRules(): bool
    {
        return To::bool($this->getData(self::DISCARD_SUBSEQUENT_RULES));
    }

    /** @inerhitDoc */
    public function setDiscardSubsequentRules(bool $discardSubsequentRules): void
    {
        $this->setData(self::DISCARD_SUBSEQUENT_RULES, $discardSubsequentRules);
    }


    /** @inerhitDoc */
    public function getPriority(): int
    {
        return To::int($this->getData(self::PRIORITY));
    }

    /** @inerhitDoc */
    public function setPriority(int $priority)
    {
        $this->setData(self::PRIORITY, $priority);
        return $this;
    }

    /** @inerhitDoc */
    public function getIsRss(): bool
    {
        return To::bool($this->getData(self::IS_RSS));
    }

    /** @inerhitDoc */
    public function setIsRss(bool $isRss): void
    {
        $this->setData(self::IS_RSS, $isRss);
    }

    /** @inerhitDoc */
    public function getType(): string
    {
        return (string)$this->getData(self::TYPE);
    }

    /** @inerhitDoc */
    public function setType(string $type): void
    {
        $this->setData(self::TYPE, $type);
    }

    /** @inerhitDoc */
    public function getValue(): ?float
    {
        return $this->getData(self::VALUE) === null ? null
            : To::float($this->getData(self::VALUE));
    }

    /** @inerhitDoc */
    public function setValue(?float $value): void
    {
        $this->setData(self::VALUE, $value);
    }

    /** @inerhitDoc */
    public function getMaxQuantity(): ?float
    {
        return $this->getData(self::MAX_QUANTITY) === null ? null
            : To::float($this->getData(self::MAX_QUANTITY));
    }

    /** @inerhitDoc */
    public function setMaxQuantity(?float $maxQuantity): void
    {
        $this->setData(self::MAX_QUANTITY, $maxQuantity);
    }

    /** @inerhitDoc */
    public function getQuantityStep(): ?int
    {
        return $this->getData(self::QUANTITY_STEP) === null ? null
            : To::int($this->getData(self::QUANTITY_STEP));
    }

    /** @inerhitDoc */
    public function setQuantityStep(?int $quantityStep): void
    {
        $this->setData(self::QUANTITY_STEP, $quantityStep);
    }

    /** @inerhitDoc */
    public function getMinPurchaseAmount(): ?float
    {
        return $this->getData(self::MIN_PURCHASE_AMOUNT) === null ? null
            : To::float($this->getData(self::MIN_PURCHASE_AMOUNT));
    }

    /** @inerhitDoc */
    public function setMinPurchaseAmount(?float $minPurchaseAmount): void
    {
        $this->setData(self::MIN_PURCHASE_AMOUNT, $minPurchaseAmount);
    }

    /** @inerhitDoc */
    public function getCategories(): ?array
    {
        return $this->getData(self::CATEGORIES);
    }

    /** @inerhitDoc */
    public function setCategories(array $categories): void
    {
        $this->setData(self::CATEGORIES, $categories);
    }

    /** @inerhitDoc */
    public function getProducts(): ?array
    {
        return $this->getData(self::PRODUCTS);
    }

    /** @inerhitDoc */
    public function setProducts(array $products): void
    {
        $this->setData(self::PRODUCTS, $products);
    }

    /** @inerhitDoc */
    public function getExpirationDate(): string
    {
        return (string)$this->getData(self::EXPIRATION_DATE);
    }

    /** @inerhitDoc */
    public function setExpirationDate(?string $expirationDate): void
    {
        $this->setData(self::EXPIRATION_DATE, $expirationDate);
    }

    /** @inerhitDoc */
    public function getTotalLimit(): int
    {
        return To::int($this->getData(self::TOTAL_LIMIT));
    }

    /** @inerhitDoc */
    public function setTotalLimit(int $totalLimit): void
    {
        $this->setData(self::TOTAL_LIMIT, $totalLimit);
    }

    /** @inerhitDoc */
    public function getPerCustomerLimit(): int
    {
        return To::int($this->getData(self::PER_CUSTOMER_LIMIT));
    }

    /** @inerhitDoc */
    public function setPerCustomerLimit(int $perCustomerLimit): void
    {
        $this->setData(self::PER_CUSTOMER_LIMIT, $perCustomerLimit);
    }

    /**
     * @inheritDoc
     */
    public function setWebsiteId(int $websiteId): void
    {
        $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteId(): int
    {
        return To::int($this->getData(self::WEBSITE_ID));
    }

    /**
     * @inheritDoc
     */
    public function validate(): string
    {
        if ($this == null) {
            return 'Price rule cannot be null';
        }
        if (empty($this->getName())) {
            return 'Rule name cannot be empty';
        }

        if (!empty($this->getProducts()) && !empty($this->getCategories())) {
            return 'Either product or category filter must be specified';
        }

        $type = $this->getType();
        switch ($type) {
            case PriceRuleInterface::TYPE_FIXED_AMOUNT:
            case PriceRuleInterface::TYPE_PERCENTAGE:
                $value = $this->getValue();
                if (empty($value)) {
                    return sprintf('The value of a %s rule cannot be empty', $type);
                }
                $value = To::float($value);
                if ($value > 100) {
                    $this->setValue(100);
                }
                break;
            case PriceRuleInterface::TYPE_FREE_SHIPPING:
                return '';
            case '':
                return 'Rule type cannot be empty';
            default:
                return sprintf('Unsupported rule type: %s', $type);
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return sprintf('Created by Ortto: %s (%s)', $this->getName(), $this->getType());
    }
}
