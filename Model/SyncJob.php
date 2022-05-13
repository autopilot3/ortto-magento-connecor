<?php
declare(strict_types=1);

namespace Ortto\Connector\Model;

use Ortto\Connector\Api\Data\SyncJobInterface;
use Ortto\Connector\Api\SchemaInterface;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Model\ResourceModel\SyncJob as ResourceModel;
use DateTime;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class SyncJob extends AbstractModel implements SyncJobInterface, IdentityInterface
{
    const CACHE_TAG = SchemaInterface::TABLE_SYNC_JOBS;

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @var string
     */
    protected $_eventPrefix = SchemaInterface::TABLE_SYNC_JOBS;

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
        return To::int($this->getData(self::ID));
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
        return To::int($this->getData(self::SCOPE_ID));
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
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getFinishedAt()
    {
        return $this->getData(self::FINISHED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setFinishedAt(DateTime $finishedAt)
    {
        return $this->setData(self::FINISHED_AT, $finishedAt);
    }

    /**
     * @inheritDoc
     */
    public function getStartedAt()
    {
        return $this->getData(self::STARTED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setStartedAt(DateTime $startedAt)
    {
        return $this->setData(self::STARTED_AT, $startedAt);
    }

    /**
     * @inheritDoc
     */
    public function getCount()
    {
        return $this->getData(self::COUNT);
    }

    /**
     * @inheritDoc
     */
    public function setCount(int $count)
    {
        return $this->setData(self::COUNT, $count);
    }

    /**
     * @inheritDoc
     */
    public function getTotal()
    {
        return $this->getData(self::TOTAL);
    }

    /**
     * @inheritDoc
     */
    public function setTotal(int $total)
    {
        return $this->setData(self::TOTAL, $total);
    }

    /**
     * @inheritDoc
     */
    public function getError(): ?string
    {
        return $this->getData(self::ERROR);
    }

    /**
     * @inheritDoc
     */
    public function setError(?string $error)
    {
        return $this->setData(self::ERROR, $error);
    }

    public function getMetadata()
    {
        return $this->getData(self::METADATA);
    }

    public function setMetadata(?string $metadata)
    {
        return $this->setData(self::METADATA, $metadata);
    }
}
