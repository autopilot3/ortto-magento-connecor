<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model;

use Autopilot\AP3Connector\Api\Data\CronCheckpointInterface;
use Autopilot\AP3Connector\Model\ResourceModel\CronCheckpoint as ResourceModel;
use Autopilot\AP3Connector\Setup\SchemaInterface;
use DateTime;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class CronCheckpoint extends AbstractModel implements CronCheckpointInterface, IdentityInterface
{
    const CACHE_TAG = SchemaInterface::TABLE_CRON_CHECKPOINT;

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @var string
     */
    protected $_eventPrefix = SchemaInterface::TABLE_CRON_CHECKPOINT;

    /**
     * @inheritdoc
     */
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
    public function getCategory()
    {
        return $this->getData(self::CATEGORY);
    }

    /**
     * @inheritDoc
     */
    public function setCategory(string $category)
    {
        return $this->setData(self::CATEGORY, $category);
    }

    /**
     * @inheritDoc
     */
    public function getScopeType()
    {
        return $this->getData(self::SCOPE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setScopeType(string $scopeType)
    {
        return $this->setData(self::SCOPE_TYPE, $scopeType);
    }

    /**
     * @inheritDoc
     */
    public function getScopeId()
    {
        return $this->getData(self::SCOPE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setScopeId(int $scopeId)
    {
        return $this->setData(self::SCOPE_ID, $scopeId);
    }

    /**
     * @inheritDoc
     */
    public function getCheckedAt()
    {
        $value = $this->getData(self::LAST_CHECKED_AT);
        switch (true) {
            case $value instanceof DateTime:
                return $value;
            case is_string($value):
                return date_create($value);
            default:
                return date_create("1800-1-1");
        }
    }

    /**
     * @inheritDoc
     */
    public function setCheckedAt(DateTime $checkedAt)
    {
        return $this->setData(self::LAST_CHECKED_AT, $checkedAt);
    }
}
