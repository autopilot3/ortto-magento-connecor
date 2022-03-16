<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model;

use Autopilot\AP3Connector\Api\Data\CronCheckpointInterface;
use Autopilot\AP3Connector\Api\SchemaInterface;
use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Model\ResourceModel\CronCheckpoint as ResourceModel;
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
    public function getCategory()
    {
        return $this->getData(self::CATEGORY);
    }

    /**
     * @inheritDoc
     */
    public function setCategory(string $category)
    {
        $this->setData(self::CATEGORY, $category);
        return $this;
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
        $this->setData(self::SCOPE_TYPE, $scopeType);
        return $this;
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
        $this->setData(self::SCOPE_ID, $scopeId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCheckedAt()
    {
        $value = $this->getData(self::LAST_CHECKED_AT);
        switch (true) {
            case $value instanceof DateTime:
                return $value->format(Config::DB_DATE_TIME_FORMAT);
            case is_string($value):
                return date(Config::DB_DATE_TIME_FORMAT, strtotime($value));
            default:
                return date(Config::DB_DATE_TIME_FORMAT, 0);
        }
    }

    /**
     * @inheritDoc
     */
    public function setCheckedAt(DateTime $checkedAt)
    {
        $this->setData(self::LAST_CHECKED_AT, $checkedAt);
        return $this;
    }
}
