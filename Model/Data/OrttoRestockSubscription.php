<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoRestockSubscriptionInterface;

class OrttoRestockSubscription extends DataObject implements OrttoRestockSubscriptionInterface
{
    /** @inheirtDoc */
    public function setProduct($product)
    {
        return $this->setData(self::PRODUCT, $product);
    }

    /** @inheirtDoc */
    public function getProduct()
    {
        return $this->getData(self::PRODUCT);
    }

    /** @inheirtDoc */
    public function setCustomer($customer)
    {
        return $this->setData(self::CUSTOMER, $customer);
    }

    /** @inheirtDoc */
    public function getCustomer()
    {
        return $this->getData(self::CUSTOMER);
    }

    /** @inheirtDoc */
    public function setDateAdded($dateAdded)
    {
        return $this->setData(self::DATE_ADDED, $dateAdded);
    }

    /** @inheirtDoc */
    public function getDateAdded()
    {
        return (string)$this->getData(self::DATE_ADDED);
    }
}
