<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Controller\Adminhtml\Sync;

use Autopilot\AP3Connector\Api\RoutesInterface;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Controller\Adminhtml\AbstractBackendJsonController;
use Autopilot\AP3Connector\Helper\To;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\ResourceModel\SyncJob\Collection as JobCollection;
use Autopilot\AP3Connector\Model\ResourceModel\SyncJob\CollectionFactory as JobCollectionFactory;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Autopilot\AP3Connector\Api\SyncCategoryInterface as JobCategory;

class Orders extends AbstractBackendJsonController
{
    /**
     * Authorization level of a basic admin session
     */
    private AutopilotLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;
    private JobCollectionFactory $jobCollectionFactory;

    public function __construct(
        Context $context,
        AutopilotLoggerInterface $logger,
        ScopeManagerInterface $scopeManager,
        JobCollectionFactory $jobCollectionFactory
    ) {
        parent::__construct($context, $logger);
        $this->logger = $logger;
        $this->scopeManager = $scopeManager;
        $this->jobCollectionFactory = $jobCollectionFactory;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $request = $this->getRequest();
        $params = $request->getParams();
        $this->logger->debug("Request received: " . $this->getUrl(RoutesInterface::MG_SYNC_ORDERS), $params);
        $scope = $this->scopeManager->getCurrentConfigurationScope($params['scope_type'], To::int($params['scope_id']));

        if (!$scope->isExplicitlyConnected()) {
            return $this->error(sprintf(
                '%s %s is not connected to Autopilot.',
                $scope->getName(),
                $scope->getType()
            ));
        }

        $jobCollection = $this->jobCollectionFactory->create();
        if ($jobCollection instanceof JobCollection) {
            $job = $jobCollection->getActiveScopeJob(JobCategory::ORDER, $scope);
            if ($job) {
                $message = sprintf(
                    'Another job is already in "%s" state [Job ID=%d].',
                    $job->getStatus(),
                    $job->getId()
                );
                return $this->error($message);
            }
            try {
                $jobCollection->enqueueNewScopeJob(JobCategory::ORDER, $scope);
            } catch (Exception $e) {
                return $this->error("Failed to add a new job to the queue!", $e);
            }
            return $this->successMessage("A new order synchronization job has been queued.");
        }
        return $this->error("Failed to initialise a new sync job.", new Exception("Invalid job collection type"));
    }
}
