<?php

namespace Autopilot\AP3Connector\Helper;

use Autopilot\AP3Connector\Model\Scope;
use Autopilot\AP3Connector\Model\ScopeType;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = "autopilot/general/enabled";
    const XML_PATH_API_KEY = "autopilot/general/apikey";
    const XML_PATH_LOG_ENABLED = "autopilot/general/logs";
    const XML_PATH_AUTHENTICATION_URL = "autopilot/general/authentication_url";
    const XML_PATH_CLIENT_ID = "autopilot/general/client_id";

    private EncryptorInterface $encryptor;
    private string $authenticationURL = "https://magento-integration-api.autopilotapp.com/-/installation/auth";
    private string $clientID = "mgqQkvCJWDFnxJTgQwfVuYEdQRWVAywE";
    private StoreManagerInterface $storeManager;

    public function __construct(Context $context, EncryptorInterface $encryptor, StoreManagerInterface $storeManager)
    {
        parent::__construct($context);
        $this->encryptor = $encryptor;
        $this->storeManager = $storeManager;
        $this->_request = $context->getRequest();
    }

    /**
     * @param Scope $scope
     * @return bool
     */
    public function isEnabled(Scope $scope): bool
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLED, $scope->getType(), $scope->getId());
    }

    /**
     * @param Scope $scope
     * @return string
     */
    public function getAPIKey(Scope $scope): string
    {
        $encrypted = trim($this->scopeConfig->getValue(self::XML_PATH_API_KEY, $scope->getType(), $scope->getId()));
        if (empty($encrypted)) {
            return "";
        }
        return $this->encryptor->decrypt($encrypted);
    }

    /**
     * @return string
     */
    public function getAuthenticationURL(): string
    {
        $url = $this->scopeConfig->getValue(self::XML_PATH_AUTHENTICATION_URL);
        if (empty($url)) {
            return $this->authenticationURL;
        }
        return $url;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        $clientID = $this->scopeConfig->getValue(self::XML_PATH_CLIENT_ID);
        if (empty($clientID)) {
            return $this->clientID;
        }
        return $clientID;
    }

    /**
     * @return Scope
     */
    public function getScope(): Scope
    {
        try {
            $websiteID = $this->_request->getParam(ScopeType::WEBSITE, 0);
            if ($websiteID > 0) {
                $website = $this->storeManager->getWebsite($websiteID);
                $code = $website->getCode();
                $scope = new Scope(ScopeType::WEBSITE, $websiteID, $website->getName(), $code);
                $websites = $this->storeManager->getWebsites();
                $count = 0;
                foreach ($websites as $w) {
                    if ($w->getCode() === $code) {
                        $count++;
                    }
                }
                $scope->setIsUnique($count === 1);
                return $scope;
            }

            $storeID = $this->_request->getParam(ScopeType::STORE, 0);
            if ($storeID > 0) {
                $store = $this->storeManager->getStore($storeID);
                $code = $store->getCode();
                $scope = new Scope(ScopeType::STORE, $storeID, $store->getName(), $code);
                $stores = $this->storeManager->getStores();
                $count = 0;
                foreach ($stores as $s) {
                    if ($s->getCode() === $code) {
                        $count++;
                    }
                }
                $scope->setIsUnique($count === 1);
                return $scope;
            }
            return new Scope(ScopeType::DEFAULT, 0);
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->_logger->error("Failed to get store/website scope", ['Exception' => $e]);
            return new Scope("", -1);
        }
    }
}
