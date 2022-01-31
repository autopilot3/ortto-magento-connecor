<?php


namespace Autopilot\AP3Connector\Helper;


use Autopilot\AP3Connector\Logger\Logger;
use Autopilot\AP3Connector\Model\Scope;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ScopeManager extends AbstractHelper
{
    private StoreManagerInterface $storeManager;
    private EncryptorInterface $encryptor;
    private Logger $logger;


    public function __construct(Context $context, StoreManagerInterface $storeManager, EncryptorInterface $encryptor, Logger $logger)
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
    }

    /**
     * @param int|null $websiteId
     * @param int|null $storeId
     * @return Scope[]
     */
    public function getActiveScopes(?int $websiteId, ?int $storeId): array
    {
        $result = array();

        $websiteAPIKey = '';
        try {
            if ($websiteId !== null) {
                $scope = new Scope($this->encryptor, $this->scopeConfig, ScopeInterface::SCOPE_WEBSITE, $websiteId);
                if ($scope->isActive()) {
                    $websiteAPIKey = $scope->getAPIKey();
                    if (!empty($websiteAPIKey)) {
                        $website = $this->storeManager->getWebsite($websiteId);
                        $scope->setName($website->getName())->setCode($website->getCode());
                        $result[] = $scope;
                    }
                }
            }
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->logger->error($e);
        }

        try {
            if ($storeId !== null) {
                $scope = new Scope($this->encryptor, $this->scopeConfig, ScopeInterface::SCOPE_WEBSITE, $storeId);
                if ($scope->isActive()) {
                    $storeAPIKey = $scope->getAPIKey();
                    if (!empty($storeAPIKey) && $storeAPIKey !== $websiteAPIKey) {
                        $store = $this->storeManager->getstore($storeId);
                        $scope->setName($store->getName())->setCode($store->getCode());
                        $result[] = $scope;
                    }
                }
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e);
        }

        return $result;
    }

    /**
     * @return Scope
     */
    public function getCurrentConfigurationScope(): Scope
    {
        try {
            $websiteID = $this->_request->getParam(ScopeInterface::SCOPE_WEBSITE, 0);
            if ($websiteID > 0) {
                $website = $this->storeManager->getWebsite($websiteID);
                $scope = new Scope($this->encryptor, $this->scopeConfig, ScopeInterface::SCOPE_WEBSITE, $websiteID);
                $scope->setName($website->getName());
                $websites = $this->storeManager->getWebsites();

                $count = 0;
                $code = $website->getCode();
                foreach ($websites as $w) {
                    if ($w->getCode() === $code) {
                        $count++;
                    }
                }

                if ($count > 1) {
                    $code .= '_' . $websiteID;
                }
                $scope->setCode($code);
                return $scope;
            }

            $storeID = $this->_request->getParam(ScopeInterface::SCOPE_STORE, 0);
            if ($storeID > 0) {
                $store = $this->storeManager->getStore($storeID);
                $scope = new Scope($this->encryptor, $this->scopeConfig, ScopeInterface::SCOPE_STORE, $storeID);
                $scope->setName($store->getName());
                $stores = $this->storeManager->getStores();

                $count = 0;
                $code = $store->getCode();
                foreach ($stores as $s) {
                    if ($s->getCode() === $code) {
                        $count++;
                    }
                }
                if ($count > 1) {
                    $code .= '_' . $storeID;
                }
                $scope->setCode($code);
                return $scope;
            }
            return new Scope($this->encryptor, $this->scopeConfig, "", -1);
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->logger->error($e);
            return new Scope($this->encryptor, $this->scopeConfig, "", -1);
        }
    }
}
