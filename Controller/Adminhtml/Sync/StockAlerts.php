<?php
declare(strict_types=1);

namespace Ortto\Connector\Controller\Adminhtml\Sync;

use Ortto\Connector\Api\RoutesInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Api\SyncJobRepositoryInterface;
use Ortto\Connector\Controller\Adminhtml\AbstractBackendJsonController;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Ortto\Connector\Api\SyncCategoryInterface as JobCategory;
use Exception;

class StockAlerts extends AbstractBackendJsonController
{
    /**
     * Authorization level of a basic admin session
     */
    private OrttoLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;
    private SyncJobRepositoryInterface $jobRepository;

    public function __construct(
        Context $context,
        OrttoLoggerInterface $logger,
        ScopeManagerInterface $scopeManager,
        SyncJobRepositoryInterface $jobRepository
    ) {
        parent::__construct($context, $logger);
        $this->logger = $logger;
        $this->scopeManager = $scopeManager;
        $this->jobRepository = $jobRepository;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $request = $this->getRequest();
        $params = $request->getParams();
        $this->logger->debug("Request received: " . $this->getUrl(RoutesInterface::MG_SYNC_STOCK_ALERTS), $params);
        $scope = $this->scopeManager->getCurrentConfigurationScope($params['scope_type'], To::int($params['scope_id']));

        if (!$scope->isExplicitlyConnected()) {
            return $this->error(sprintf(
                '%s %s is not connected to Ortto.',
                $scope->getName(),
                $scope->getType()
            ));
        }

        $job = $this->jobRepository->getActiveScopeJob(JobCategory::STOCK_ALERT, $scope);
        if ($job) {
            $message = sprintf(
                'Another job is already in "%s" state [Job ID=%d].',
                $job->getStatus(),
                $job->getEntityId()
            );
            return $this->error($message);
        }
        try {
            $this->jobRepository->enqueueNewScopeJob(JobCategory::STOCK_ALERT, $scope);
        } catch (Exception $e) {
            return $this->error("Failed to add a new job to the queue!", $e);
        }
        return $this->successMessage("A new synchronization job has been queued.");
    }
}
