<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Model;

use Autopilot\AP3Connector\Api\Data\CustomerAttributesInterface;
use Autopilot\AP3Connector\Setup\SchemaInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Autopilot\AP3Connector\Model\ResourceModel\CustomerAttributes as ResourceModel;

class CustomerAttributes extends AbstractModel implements CustomerAttributesInterface, IdentityInterface
{
    const CACHE_TAG = SchemaInterface::TABLE_CUSTOMER_ATTRIBUTES;

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($value)
    {
        return $this->setData(self::ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getAutopilotContactId(): string
    {
        return (string)$this->getData(self::CONTACT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAutopilotContactId(string $contactId)
    {
        $this->setData(self::CONTACT_ID, $contactId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId)
    {
        $this->setData(self::CUSTOMER_ID, $customerId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): int
    {
        return (int)$this->getData(self::CUSTOMER_ID);
    }
}
