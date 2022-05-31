<?php

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\DiscountInterface;
use Ortto\Connector\Helper\To;

class Discount extends DataObject implements DiscountInterface
{
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
    public function getRuleId(): int
    {
        return To::int($this->getData(self::RULE_ID));
    }

    /** @inerhitDoc */
    public function setRuleId(int $ruleId): void
    {
        $this->setData(self::RULE_ID, $ruleId);
    }

    /** @inerhitDoc */
    public function validate(): string
    {
        if ($this->getRuleId() === 0) {
            return 'Rule ID must be specified';
        }
        return '';
    }
}
