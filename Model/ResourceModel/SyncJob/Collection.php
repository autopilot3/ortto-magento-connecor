<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model\ResourceModel\SyncJob;

use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\Data\SyncJobInterface;
use Autopilot\AP3Connector\Api\Data\SyncJobInterface as Job;
use Autopilot\AP3Connector\Api\JobStatusInterface as Status;
use Autopilot\AP3Connector\Model\ResourceModel\SyncJob as ResourceModel;
use Autopilot\AP3Connector\Model\SyncJobFactory;
use Autopilot\AP3Connector\Model\SyncJob as Model;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Exception;

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
        SyncJobFactory $cronCheckpointFactory,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->syncJobFactory = $cronCheckpointFactory;
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
     * @return SyncJobInterface[]
     */
    public function getQueuedJobs(string $category)
    {
        return $this->addFieldToSelect('*')
            ->addFieldToFilter(Job::CATEGORY, $category)
            ->addFieldToFilter(Job::STATUS, Status::QUEUED)
            ->load()->getItems();
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

    /**
     * @throws NoSuchEntityException
     */
    public function markAsInProgress(int $jobId)
    {
        $job = $this->getJobById($jobId);
        $job->setStatus(Status::IN_PROGRESS);
        $job->setStartedAt($this->time->date());
        $this->save();
    }

    /**
     * @throws NoSuchEntityException
     */
    public function markAsFailed(int $jobId, string $error, string $metadata = "")
    {
        $job = $this->getJobById($jobId);
        $job->setStatus(Status::FAILED);
        $job->setError($error);
        $job->setFinishedAt($this->time->date());
        $job->setMetadata($metadata);
        $this->save();
    }

    /**
     * @throws NoSuchEntityException
     */
    public function updateStats(int $jobId, int $total, int $count, string $metadata = "")
    {
        $job = $this->getJobById($jobId);
        $job->setTotal($total);
        $job->setCount($job->getCount() + $count);
        $job->setMetadata($metadata);
        $this->save();
    }

    /**
     * @throws NoSuchEntityException
     */
    public function markAsDone(int $jobId, string $metadata = "")
    {
        $job = $this->getJobById($jobId);
        $job->setStatus(Status::SUCCESS);
        $job->setFinishedAt($this->time->date());
        $job->setMetadata($metadata);
        $this->save();
    }

    /**
     * @param int $jobId
     * @return SyncJobInterface
     * @throws NoSuchEntityException
     */
    public function getJobById(int $jobId)
    {
        /**
         * @var $job SyncJobInterface
         */
        $job = $this->addFieldToSelect('*')->getItemById($jobId);
        if ($job === null) {
            throw new NoSuchEntityException(new Phrase(sprintf("Job ID %d not found", $jobId)));
        }
        return $job;
    }
}
