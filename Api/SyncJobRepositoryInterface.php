<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tests\NamingConvention\true\mixed;
use Ortto\Connector\Api\Data\SyncJobInterface;

/**
 *  Interface DiscountRepositoryInterface
 * @api
 */
interface SyncJobRepositoryInterface
{
    /**
     * @param string $category
     * @return SyncJobInterface[]
     */
    public function getQueuedJobs(string $category): array;

    /**
     * @param string $category
     * @param ConfigScopeInterface $scope
     * @return bool|SyncJobInterface
     */
    public function getActiveScopeJob(string $category, ConfigScopeInterface $scope);

    /**
     * @param string $category
     * @param ConfigScopeInterface $scope
     * @return void
     * @throws Exception
     */
    public function enqueueNewScopeJob(string $category, ConfigScopeInterface $scope);

    /**
     * @throws NoSuchEntityException
     */
    public function markAsInProgress(int $jobId);

    /**
     * @throws NoSuchEntityException
     */
    public function markAsFailed(int $jobId, string $error, string $metadata = "");

    /**
     * @throws NoSuchEntityException
     */
    public function updateStats(int $jobId, int $total, int $count, string $metadata = "");

    /**
     * @throws NoSuchEntityException
     */
    public function markAsDone(int $jobId, string $metadata = "");

    /**
     * @param int $jobId
     * @return SyncJobInterface
     * @throws NoSuchEntityException
     */
    public function getJobById(int $jobId);

    /**
     * @param mixed $jobId
     * @return void
     */
    public function deleteById($jobId): void;
}
