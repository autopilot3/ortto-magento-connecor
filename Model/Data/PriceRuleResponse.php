<?php

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\PriceRuleResponseInterface;
use Ortto\Connector\Helper\To;

class PriceRuleResponse extends DataObject implements PriceRuleResponseInterface
{
    /**
     * Getter for Id.
     *
     * @return int
     */
    public function getId(): int
    {
        return To::int($this->getData(self::ID));
    }

    /**
     * Setter for Id.
     *
     * @param int $id
     *
     * @return void
     */
    public function setId(int $id): void
    {
        $this->setData(self::ID, $id);
    }
}
