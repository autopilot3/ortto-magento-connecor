<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Controller\Adminhtml\Sync;

use Autopilot\AP3Connector\Api\RoutesInterface;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Helper\To;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\ResourceModel\SyncJob\Collection as JobCollection;
use Autopilot\AP3Connector\Model\ResourceModel\SyncJob\CollectionFactory as JobCollectionFactory;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Autopilot\AP3Connector\Api\SyncCategoryInterface as JobCategory;

class Orders extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    private JsonFactory $jsonFactory;
    private AutopilotLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;
    private JobCollectionFactory $jobCollectionFactory;

    private Data $helper;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        AutopilotLoggerInterface $logger,
        ScopeManagerInterface $scopeManager,
        Data $helper,
        JobCollectionFactory $jobCollectionFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->scopeManager = $scopeManager;
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->helper = $helper;
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
        $result = $this->jsonFactory->create();

        if (!$scope->isConnected()) {
            $this->logger->warn("Not connected to Autopilot", $scope->toArray());
            $result->setData($this->helper->getErrorResponse(sprintf(
                '%s %s is not connected to Autopilot.',
                $scope->getName(),
                $scope->getType()
            )));
            return $result;
        }

        $jobCollection = $this->jobCollectionFactory->create();
        if ($jobCollection instanceof JobCollection) {
            $job = $jobCollection->getActiveScopeJob(JobCategory::ORDER, $scope);
            if ($job) {
                $msg = sprintf('Another job is already in "%s" state [Job ID=%d].', $job->getStatus(), $job->getId());
                $result->setData($this->helper->getErrorResponse($msg));
                return $result;
            }
            try {
                $jobCollection->enqueueNewScopeJob(JobCategory::ORDER, $scope);
            } catch (Exception $e) {
                $this->logger->error($e, "Failed to enqueue a new order sync job");
                $result->setData($this->helper->getErrorResponse("Failed to add a new job to the queue!"));
                return $result;
            }
            $result->setData([
                'message' => "A new order synchronization job has been queued.",
            ]);
            return $result;
        }
        $this->logger->error(new Exception("Invalid job collection type"));
        $result->setData($this->helper->getErrorResponse("Failed to initialise a new sync job."));

        return $result;
    }
}
