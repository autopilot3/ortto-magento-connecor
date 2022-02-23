<?php


namespace Autopilot\AP3Connector\Helper;

use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\Scope;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ScopeManager extends AbstractHelper implements ScopeManagerInterface
{
    private StoreManagerInterface $storeManager;
    private EncryptorInterface $encryptor;
    private AutopilotLoggerInterface $logger;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        AutopilotLoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
    }

    public function getActiveScopes(): array
    {
        $result = [];

        $stores = $this->storeManager->getWebsites();
        foreach ($stores as $website) {
            $scope = new Scope($this->encryptor, $this->scopeConfig, $this->storeManager);
            try {
                $scope->load(ScopeInterface::SCOPE_WEBSITE, $website->getId());
                if ($scope->isConnected()) {
                    $result[] = $scope;
                }
            } catch (NoSuchEntityException|LocalizedException|NotFoundException $e) {
                $this->logger->error($e);
            }
        }

        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $scope = new Scope($this->encryptor, $this->scopeConfig, $this->storeManager);
            try {
                $scope->load(ScopeInterface::SCOPE_STORE, $store->getId());
                if ($scope->isConnected()) {
                    $result[] = $scope;
                }
            } catch (NoSuchEntityException|LocalizedException|NotFoundException $e) {
                $this->logger->error($e);
            }
        }

        return $result;
    }

    public function getCurrentConfigurationScope(string $scopeType = '', int $scopeId = null): Scope
    {
        if (empty($scopeType)) {
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
        }
        try {
            if ($scopeType === ScopeInterface::SCOPE_WEBSITE) {
                if (empty($scopeId)) {
                    $websiteId = $this->_request->getParam($scopeType, -1);
                } else {
                    $websiteId = $scopeId;
                }
                if ($websiteId != -1) {
                    $scope = new Scope($this->encryptor, $this->scopeConfig, $this->storeManager);
                    $scope->load($scopeType, $websiteId);
                    return $scope;
                }
            }

            $scopeType = ScopeInterface::SCOPE_STORE;
            if (empty($scopeId)) {
                $storeId = $this->_request->getParam($scopeType, -1);
            } else {
                $storeId = $scopeId;
            }
            if ($storeId != -1) {
                $scope = new Scope($this->encryptor, $this->scopeConfig, $this->storeManager);
                $scope->load($scopeType, $storeId);
                return $scope;
            }
        } catch (NoSuchEntityException|LocalizedException|NotFoundException $e) {
            $this->logger->error($e);
            return new Scope($this->encryptor, $this->scopeConfig, $this->storeManager);
        }
        return new Scope($this->encryptor, $this->scopeConfig, $this->storeManager);
    }
}
