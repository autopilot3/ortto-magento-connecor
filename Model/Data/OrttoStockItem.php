<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoStockItemInterface;
use Ortto\Connector\Helper\To;

class OrttoStockItem extends DataObject implements OrttoStockItemInterface
{

    /** @inheirtDoc */
    public function setQuantity($quantity)
    {
        return $this->setData(self::QUANTITY, $quantity);
    }

    /** @inheirtDoc */
    public function getQuantity()
    {
        return To::float($this->getData(self::QUANTITY));
    }

    /** @inheirtDoc */
    public function setIsInStock($isInStock)
    {
        return $this->setData(self::IS_IN_STOCK, $isInStock);
    }

    /** @inheirtDoc */
    public function getIsInStock()
    {
        return To::bool($this->getData(self::IS_IN_STOCK));
    }

    /** @inheirtDoc */
    public function setIsSalable($isSalable)
    {
        return $this->setData(self::IS_SALABLE, $isSalable);
    }

    /** @inheirtDoc */
    public function getIsSalable()
    {
        return To::bool($this->getData(self::IS_SALABLE));
    }
}
