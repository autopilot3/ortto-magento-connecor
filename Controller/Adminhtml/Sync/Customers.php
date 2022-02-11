<?php

namespace Autopilot\AP3Connector\Controller\Adminhtml\Sync;

use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use AutoPilot\AP3Connector\Model\SyncJobFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class Customers extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    private JsonFactory $jsonFactory;
    private AutopilotLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;
    private SyncJobFactory $syncJobFactory;
    private Data $helper;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        AutopilotLoggerInterface $logger,
        ScopeManagerInterface $scopeManager,
        Data $helper,
        SyncJobFactory $syncJobFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->scopeManager = $scopeManager;
        $this->syncJobFactory = $syncJobFactory;
        $this->helper = $helper;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $request = $this->getRequest();
        $params = $request->getParams();
        $this->logger->debug("Request received: " . $this->getUrl(Config::SYNC_CUSTOMERS_ROUTE), $params);
        $scope = $this->scopeManager->getCurrentConfigurationScope($params['scope_type'], $params['$scope_id']);
        $result = $this->jsonFactory->create();

        if (!$scope->isConnected()) {
            $this->logger->warn("The extension is not connected to Autopilot");
            $result->setData($this->helper->getErrorResponse("You are not connected to Autopilot yet!"));
            return $result;
        }

        $jobFactory = $this->syncJobFactory->create();

        $result->setData([
            'message' => "Customer synchronization job has been queued",
        ]);

        return $result;
    }
}
