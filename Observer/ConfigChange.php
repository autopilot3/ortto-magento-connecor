<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Observer;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
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
    private ConfigScopeInterface $scope;

    public function __construct(
        AutopilotLoggerInterface $logger,
        ConfigScopeInterface $scope,
        AutopilotClientInterface $autopilotClient
    ) {
        $this->logger = $logger;
        $this->scope = $scope;
        $this->autopilotClient = $autopilotClient;
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
            $this->scope->load($scopeType, $scopeId);
        } catch (NoSuchEntityException|NotFoundException|LocalizedException $e) {
            $this->logger->error($e, "Failed to load the scope");
            return;
        }
        $this->autopilotClient->updateAccessToken($this->scope);
    }
}
