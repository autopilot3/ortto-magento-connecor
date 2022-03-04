<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Observer;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Store\Model\ScopeInterface;

class ConfigChange implements ObserverInterface
{
    private AutopilotClientInterface $autopilotClient;
    private AutopilotLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;

    public function __construct(
        AutopilotLoggerInterface $logger,
        ScopeManagerInterface $scopeManager,
        AutopilotClientInterface $autopilotClient
    ) {
        $this->logger = $logger;
        $this->autopilotClient = $autopilotClient;
        $this->scopeManager = $scopeManager;
    }

    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $websiteId = $event->getData('website');

        if (!empty($websiteId)) {
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
            $scopeId = (int)$websiteId;
        } else {
            $storeId = $event->getData('scope');
            if (empty($storeId)) {
                $this->logger->warn("Undefined configuration scope");
                return;
            }
            $scopeType = ScopeInterface::SCOPE_STORE;
            $scopeId = (int)$storeId;
        }

        try {
            $scope = $this->scopeManager->initialiseScope($scopeType, $scopeId);
            $this->autopilotClient->updateAccessToken($scope);
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to load the scope or update the access token");
            return;
        }
    }
}
