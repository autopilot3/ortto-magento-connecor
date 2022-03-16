<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Observer;

use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Event\Observer;
use Exception;

class OrderPlacedAfter implements ObserverInterface
{
    private AutopilotLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;
    private AutopilotClientInterface $autopilotClient;
    private Data $helper;

    public function __construct(
        AutopilotLoggerInterface $logger,
        ScopeManagerInterface $scopeManager,
        AutopilotClientInterface $autopilotClient,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->scopeManager = $scopeManager;
        $this->autopilotClient = $autopilotClient;
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            /** @var OrderInterface $order */
            $order = $event->getData('order');
            $scopes = $this->scopeManager->getActiveScopes();
            foreach ($scopes as $scope) {
                if (!$this->helper->shouldExportOrder($scope, $order)) {
                    continue;
                }
                $this->autopilotClient->importOrders($scope, [$order]);
            }
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to export the order");
        }
    }
}
