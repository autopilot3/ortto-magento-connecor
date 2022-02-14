<?php

namespace Autopilot\AP3Connector\Model\ResourceModel\SyncJob;

use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\Data\SyncJobInterface;
use Autopilot\AP3Connector\Api\Data\SyncJobInterface as Job;
use Autopilot\AP3Connector\Api\JobStatusInterface as Status;
use Autopilot\AP3Connector\Model\ResourceModel\SyncJob as ResourceModel;
use Autopilot\AP3Connector\Model\SyncJobFactory;
use Autopilot\AP3Connector\Model\SyncJob as Model;
use Exception;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    protected $_eventPrefix = 'autopilot_sync_jobs_collection';
    protected $_idFieldName = "id";
    private SyncJobFactory $syncJobFactory;
    private TimezoneInterface $time;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        TimezoneInterface $time,
        ManagerInterface $eventManager,
        SyncJobFactory $syncJobFactory,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->syncJobFactory = $syncJobFactory;
        $this->time = $time;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    /**
     * @param string $category
     * @param ConfigScopeInterface $scope
     * @return SyncJobInterface|bool
     */
    public function getScopeJob(string $category, ConfigScopeInterface $scope)
    {
        $result = $this->addFieldToSelect('status')
            ->addFieldToFilter(Job::CATEGORY, $category)
            ->addFieldToFilter(Job::SCOPE_TYPE, $scope->getType())
            ->addFieldToFilter(Job::SCOPE_ID, $scope->getId())
            ->setPageSize(1);

        if ($result->getSize()) {
            return $result->getFirstItem();
        }

        return false;
    }

    /**
     * @param string $category
     * @param ConfigScopeInterface $scope
     * @return bool|SyncJobInterface
     */
    public function getActiveScopeJob(string $category, ConfigScopeInterface $scope)
    {
        $result = $this->addFieldToSelect('*')
            ->addFieldToFilter(Job::CATEGORY, $category)
            ->addFieldToFilter(Job::SCOPE_TYPE, $scope->getType())
            ->addFieldToFilter(Job::SCOPE_ID, $scope->getId())
            ->addFieldToFilter(Job::STATUS, ['in' => [Status::QUEUED, Status::IN_PROGRESS]])
            ->setPageSize(1);

        if ($result->getSize()) {
            return $result->getFirstItem();
        }
        return false;
    }

    /**
     * @param string $category
     * @param ConfigScopeInterface $scope
     * @return void
     * @throws Exception
     */
    public function enqueueNewScopeJob(string $category, ConfigScopeInterface $scope)
    {
        $job = $this->syncJobFactory->create();
        $job->setCategory($category);
        $job->setScopeType($scope->getType());
        $job->setScopeId($scope->getId());
        $job->setStatus(Status::QUEUED);
        $job->setCreatedAt($this->time->date());
        $this->addItem($job);
        $this->save();
    }
}
