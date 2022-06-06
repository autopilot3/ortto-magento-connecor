<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\ResourceModel;

use Exception;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\Data\SyncJobInterface;
use Ortto\Connector\Api\Data\SyncJobInterface as Job;
use Ortto\Connector\Api\JobStatusInterface as Status;
use Ortto\Connector\Api\SchemaInterface;
use Ortto\Connector\Api\SyncJobRepositoryInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Model\ResourceModel\SyncJob\CollectionFactory;
use Ortto\Connector\Model\SyncJobFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class SyncJobRepository implements SyncJobRepositoryInterface
{
    private SyncJobFactory $syncJobFactory;
    private TimezoneInterface $time;
    private OrttoLoggerInterface $logger;
    private CollectionFactory $collectionFactory;
    private Data $helper;

    public function __construct(
        OrttoLoggerInterface $logger,
        TimezoneInterface $time,
        SyncJobFactory $cronCheckpointFactory,
        CollectionFactory $collectionFactory,
        Data $helper
    ) {
        $this->syncJobFactory = $cronCheckpointFactory;
        $this->time = $time;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;

        $this->helper = $helper;
    }

    /** @inheirtDoc */
    public function getQueuedJobs(string $category): array
    {
        $collection = $this->collectionFactory->create();
        /** @var SyncJobInterface[] $items */
        $items = $collection->addFieldToSelect('*')
            ->addFieldToFilter(Job::CATEGORY, $category)
            ->addFieldToFilter(Job::STATUS, Status::QUEUED)
            ->getItems();

        return $items;
    }

    /** @inheirtDoc */
    public function getActiveScopeJob(string $category, ConfigScopeInterface $scope)
    {
        $collection = $this->collectionFactory->create();
        $result = $collection->addFieldToSelect('*')
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

    /** @inheirtDoc
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
        $collection = $this->collectionFactory->create();
        $collection->addItem($job);
        $collection->save();
    }

    /** @inheirtDoc */
    public function markAsInProgress(int $jobId)
    {
        $collection = $this->collectionFactory->create();
        $table = $collection->getTable(SchemaInterface::TABLE_SYNC_JOBS);
        $connection = $collection->getConnection();

        $condition = [
            $connection->quoteInto(sprintf('%s = ?', SyncJobInterface::ENTITY_ID), $jobId),
        ];
        $data = [
            Job::STATUS => Status::IN_PROGRESS,
            Job::STARTED_AT => $this->helper->toUTC($this->time->date()),
        ];
        $connection->update($table, $data, $condition);
    }

    /** @inheirtDoc */
    public function markAsFailed(int $jobId, string $error, string $metadata = "")
    {
        $collection = $this->collectionFactory->create();
        $table = $collection->getTable(SchemaInterface::TABLE_SYNC_JOBS);
        $connection = $collection->getConnection();

        $condition = [
            $connection->quoteInto(sprintf('%s = ?', SyncJobInterface::ENTITY_ID), $jobId),
        ];
        $data = [
            Job::STATUS => Status::FAILED,
            Job::FINISHED_AT => $this->helper->toUTC($this->time->date()),
            Job::ERROR => $error,
            Job::METADATA => $metadata,
        ];
        $connection->update($table, $data, $condition);
    }

    /** @inheirtDoc */
    public function markAsDone(int $jobId, string $metadata = "")
    {
        $collection = $this->collectionFactory->create();
        $table = $collection->getTable(SchemaInterface::TABLE_SYNC_JOBS);
        $connection = $collection->getConnection();

        $condition = [
            $connection->quoteInto(sprintf('%s = ?', SyncJobInterface::ENTITY_ID), $jobId),
        ];
        $data = [
            Job::STATUS => Status::SUCCEEDED,
            Job::FINISHED_AT => $this->helper->toUTC($this->time->date()),
            Job::METADATA => $metadata,
        ];
        $connection->update($table, $data, $condition);
    }

    /**
     * @throws NoSuchEntityException
     */
    public function updateStats(int $jobId, int $total, int $count, string $metadata = "")
    {
        $job = $this->getJobById($jobId);
        $collection = $this->collectionFactory->create();
        $table = $collection->getTable(SchemaInterface::TABLE_SYNC_JOBS);
        $connection = $collection->getConnection();

        $condition = [
            $connection->quoteInto(sprintf('%s = ?', SyncJobInterface::ENTITY_ID), $jobId),
        ];
        $data = [
            Job::TOTAL => $total,
            Job::COUNT => $job->getCount() + $count,
            Job::METADATA => $metadata,
        ];
        $connection->update($table, $data, $condition);
    }

    /** @inheirtDoc
     * @throws NoSuchEntityException
     */
    public function getJobById(int $jobId)
    {
        $collection = $this->collectionFactory->create();
        /**
         * @var $job SyncJobInterface
         */
        $job = $collection->addFieldToSelect('*')->getItemById($jobId);
        if ($job === null) {
            throw new NoSuchEntityException(new Phrase(sprintf("Job ID %d not found", $jobId)));
        }
        return $job;
    }

    /** @inheirtDoc */
    public function deleteById($jobId): void
    {
        $collection = $this->collectionFactory->create();
        $table = $collection->getTable(SchemaInterface::TABLE_SYNC_JOBS);
        $connection = $collection->getConnection();
        $condition = [
            $connection->quoteInto(sprintf('%s = ?', SyncJobInterface::ENTITY_ID), $jobId),
        ];
        $connection->delete($table, $condition);
    }
}
