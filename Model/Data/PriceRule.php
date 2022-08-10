<?php

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\PriceRuleInterface;
use Ortto\Connector\Helper\Config;
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
    public function getCode(): string
    {
        return (string)$this->getData(self::CODE);
    }

    /** @inerhitDoc */
    public function setCode(string $code): void
    {
        $this->setData(self::CODE, $code);
    }

    /** @inerhitDoc */
    public function getId(): int
    {
        return To::int($this->getData(self::ID));
    }

    /** @inerhitDoc */
    public function setId(int $id): void
    {
        $this->setData(self::ID, $id);
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
    public function getApplyFreeShippingToMatchingItemsOnly(): bool
    {
        return To::bool($this->getData(self::APPLY_FREE_SHIPPING_TO_MATCHING_ITEMS_ONLY));
    }

    /** @inerhitDoc */
    public function setApplyFreeShippingToMatchingItemsOnly(bool $applyFreeShippingToMatchingItemsOnly): void
    {
        $this->setData(self::APPLY_FREE_SHIPPING_TO_MATCHING_ITEMS_ONLY, $applyFreeShippingToMatchingItemsOnly);
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
    public function setPriority(int $priority): void
    {
        $this->setData(self::PRIORITY, $priority);
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
    public function getValue(): float
    {
        return To::float($this->getData(self::VALUE));
    }

    /** @inerhitDoc */
    public function setValue(float $value): void
    {
        $this->setData(self::VALUE, $value);
    }

    /** @inerhitDoc */
    public function getMaxQuantity(): float
    {
        return To::float($this->getData(self::MAX_QUANTITY));
    }

    /** @inerhitDoc */
    public function setMaxQuantity(float $maxQuantity): void
    {
        $this->setData(self::MAX_QUANTITY, $maxQuantity);
    }

    /** @inerhitDoc */
    public function getMinQuantity(): float
    {
        return To::float($this->getData(self::MIN_QUANTITY));
    }

    /** @inerhitDoc */
    public function setMinQuantity(float $minQuantity): void
    {
        $this->setData(self::MIN_QUANTITY, $minQuantity);
    }

    /** @inerhitDoc */
    public function getMinPurchaseAmount(): float
    {
        return To::float($this->getData(self::MIN_PURCHASE_AMOUNT));
    }

    /** @inerhitDoc */
    public function setMinPurchaseAmount(float $minPurchaseAmount): void
    {
        $this->setData(self::MIN_PURCHASE_AMOUNT, $minPurchaseAmount);
    }

    /** @inerhitDoc */
    public function getBuyXQuantity(): int
    {
        return To::int($this->getData(self::BUY_X_QUANTITY));
    }

    /** @inerhitDoc */
    public function setBuyXQuantity(int $buyXQuantity): void
    {
        $this->setData(self::BUY_X_QUANTITY, $buyXQuantity);
    }

    /** @inerhitDoc */
    public function getRuleCategories(): ?array
    {
        return $this->getData(self::RULE_CATEGORIES);
    }

    /** @inerhitDoc */
    public function setRuleCategories(?array $ruleCategories): void
    {
        $this->setData(self::RULE_CATEGORIES, $ruleCategories);
    }

    /** @inerhitDoc */
    public function getRuleProducts(): ?array
    {
        return $this->getData(self::RULE_PRODUCTS);
    }

    /** @inerhitDoc */
    public function setRuleProducts(?array $ruleProducts): void
    {
        $this->setData(self::RULE_PRODUCTS, $ruleProducts);
    }

    /** @inerhitDoc */
    public function getActionCategories(): ?array
    {
        return $this->getData(self::ACTION_CATEGORIES);
    }

    /** @inerhitDoc */
    public function setActionCategories(?array $actionCategories): void
    {
        $this->setData(self::ACTION_CATEGORIES, $actionCategories);
    }

    /** @inerhitDoc */
    public function getActionProducts(): ?array
    {
        return $this->getData(self::ACTION_PRODUCTS);
    }

    /** @inerhitDoc */
    public function setActionProducts(?array $actionProducts): void
    {
        $this->setData(self::ACTION_PRODUCTS, $actionProducts);
    }

    /** @inerhitDoc */
    public function getExpirationDate(): ?string
    {
        return $this->truncateTime($this->getData(self::EXPIRATION_DATE));
    }

    /** @inerhitDoc */
    public function setExpirationDate(?string $expirationDate): void
    {
        $this->setData(self::EXPIRATION_DATE, $expirationDate);
    }

    /** @inerhitDoc */
    public function getStartDate(): ?string
    {
        return $this->truncateTime($this->getData(self::START_DATE));
    }

    /** @inerhitDoc */
    public function setStartDate(?string $startDate): void
    {
        $this->setData(self::START_DATE, $startDate);
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

        if (!empty($this->getRuleProducts()) && !empty($this->getRuleCategories())) {
            return 'Either product or category rule filters must be specified';
        }

        if (!empty($this->getActionProducts()) && !empty($this->getActionCategories())) {
            return 'Either product or category action filters must be specified';
        }

        $type = $this->getType();
        switch ($type) {
            case PriceRuleInterface::TYPE_PERCENTAGE:
                if ($this->getValue() > 100) {
                    $this->setValue(100);
                }
                break;
            case PriceRuleInterface::TYPE_BUY_X_GET_Y_FREE:
            case PriceRuleInterface::TYPE_FIXED_EACH_ITEM:
            case PriceRuleInterface::TYPE_FIXED_CART_TOTAL:
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
        return (string)$this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setDescription(string $description)
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    private function truncateTime(?string $datetime): ?string
    {
        if (empty($datetime)) {
            return $datetime;
        }
        if ($dt = date_create($datetime)) {
            return $dt->format(Config::DATE_TIME_FORMAT);
        }

        return $datetime;
    }
}
