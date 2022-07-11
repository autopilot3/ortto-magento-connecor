<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoProductParentGroupInterface;

class OrttoProductParentGroup extends DataObject implements OrttoProductParentGroupInterface
{
    /** @inheirtDoc */
    public function setBundle(array $bundle)
    {
        return $this->setData(self::BUNDLE, $bundle);
    }

    /** @inheirtDoc */
    public function getBundle(): array
    {
        return $this->getData(self::BUNDLE) ?? [];
    }

    /** @inheirtDoc */
    public function setConfigurable(array $configurable)
    {
        return $this->setData(self::CONFIGURABLE, $configurable);
    }

    /** @inheirtDoc */
    public function getConfigurable(): array
    {
        return $this->getData(self::CONFIGURABLE) ?? [];
    }

    /** @inheirtDoc */
    public function setGrouped(array $grouped)
    {
        return $this->setData(self::GROUPED, $grouped);
    }

    /** @inheirtDoc */
    public function getGrouped(): array
    {
        return $this->getData(self::GROUPED) ?? [];
    }

    /** @inheirtDoc */
    public function serializeToArray()
    {
        if ($this == null) {
            return null;
        }
        $result=[];
        $result[self::BUNDLE] = $this->getBundle();
        $result[self::CONFIGURABLE] = $this->getConfigurable();
        $result[self::GROUPED] = $this->getGrouped();
        return $result;
    }
}
