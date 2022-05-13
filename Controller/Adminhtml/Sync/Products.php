<?php
declare(strict_types=1);

namespace Ortto\Connector\Controller\Adminhtml\Sync;

use Ortto\Connector\Api\RoutesInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Controller\Adminhtml\AbstractBackendJsonController;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Model\ResourceModel\SyncJob\Collection as JobCollection;
use Ortto\Connector\Model\ResourceModel\SyncJob\CollectionFactory as JobCollectionFactory;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Ortto\Connector\Api\SyncCategoryInterface as JobCategory;

class Products extends AbstractBackendJsonController
{
    /**
     * Authorization level of a basic admin session
     */
    private OrttoLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;
    private JobCollectionFactory $jobCollectionFactory;

    public function __construct(
        Context $context,
        OrttoLoggerInterface $logger,
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
        $this->logger->debug("Request received: " . $this->getUrl(RoutesInterface::MG_SYNC_PRODUCTS), $params);
        $scope = $this->scopeManager->getCurrentConfigurationScope($params['scope_type'], To::int($params['scope_id']));

        if (!$scope->isExplicitlyConnected()) {
            return $this->error(sprintf(
                '%s %s is not connected to Ortto.',
                $scope->getName(),
                $scope->getType()
            ));
        }

        $jobCollection = $this->jobCollectionFactory->create();
        if ($jobCollection instanceof JobCollection) {
            $job = $jobCollection->getActiveScopeJob(JobCategory::PRODUCT, $scope);
            if ($job) {
                $message = sprintf(
                    'Another job is already in "%s" state [Job ID=%d].',
                    $job->getStatus(),
                    $job->getId()
                );
                return $this->error($message);
            }
            try {
                $jobCollection->enqueueNewScopeJob(JobCategory::PRODUCT, $scope);
            } catch (Exception $e) {
                return $this->error("Failed to add a new job to the queue!", $e);
            }
            return $this->successMessage("A new product synchronization job has been queued.");
        }
        return $this->error("Failed to initialise a new sync job.", new Exception("Invalid job collection type"));
    }
}
