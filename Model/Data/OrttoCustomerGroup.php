<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoCustomerGroupInterface;

class OrttoCustomerGroup extends DataObject implements OrttoCustomerGroupInterface
{
    /**
     * @inheritDoc
     */
    public function setRegistered($customers)
    {
        return $this->setData(self::REGISTERED, $customers);
    }

    /**
     * @inheritDoc
     */
    public function getRegistered()
    {
        return $this->getData(self::REGISTERED);
    }

    /**
     * @inheritDoc
     */
    public function setAnonymous($customers)
    {
        return $this->setData(self::ANONYMOUS, $customers);
    }

    /**
     * @inheritDoc
     */
    public function getAnonymous()
    {
        return $this->getData(self::ANONYMOUS);
    }
}
