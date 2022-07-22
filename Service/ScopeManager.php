<?php
declare(strict_types=1);

namespace Ortto\Connector\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\ConfigurationReaderInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Model\Scope;
use Ortto\Connector\Model\ScopeFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Exception;

class ScopeManager implements ScopeManagerInterface
{
    private StoreManagerInterface $storeManager;
    private OrttoLoggerInterface $logger;
    private ConfigurationReaderInterface $configReader;
    private ScopeFactory $scopeFactory;
    private RequestInterface $request;
    private UrlInterface $urlInterface;

    public function __construct(
        StoreManagerInterface $storeManager,
        OrttoLoggerInterface $logger,
        ConfigurationReaderInterface $configReader,
        ScopeFactory $scopeFactory,
        RequestInterface $request,
        UrlInterface $urlInterface
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->configReader = $configReader;
        $this->scopeFactory = $scopeFactory;
        $this->request = $request;
        $this->urlInterface = $urlInterface;
    }

    public function getActiveScopes(): array
    {
        $result = [];
//        $websites = $this->storeManager->getWebsites();
//        foreach ($websites as $website) {
//            try {
//                $scope = $this->initialiseScope(ScopeInterface::SCOPE_WEBSITE, To::int($website->getId()), $websites);
//                if ($scope->isExplicitlyConnected()) {
//                    $result[] = $scope;
//                }
//            } catch (Exception $e) {
//                $this->logger->error($e, "Failed to initialise website scope");
//            }
//        }

        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            try {
                $scope = $this->initialiseScope(
                    ScopeInterface::SCOPE_STORE,
                    To::int($store->getId())
                );
                if ($scope->isExplicitlyConnected()) {
                    $result[] = $scope;
                }
            } catch (Exception $e) {
                $this->logger->error($e, "Failed to initialise store scope");
            }
        }

        return $result;
    }

    public function getCurrentConfigurationScope(string $scopeType = '', ?int $scopeId = null): Scope
    {
        if (empty($scopeType)) {
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
        }
        try {
            if ($scopeType === ScopeInterface::SCOPE_WEBSITE) {
                if (empty($scopeId)) {
                    $websiteId = To::int($this->request->getParam($scopeType, -1));
                } else {
                    $websiteId = $scopeId;
                }
                if ($websiteId != -1) {
                    return $this->initialiseScope($scopeType, $websiteId);
                }
            }

            $scopeType = ScopeInterface::SCOPE_STORE;
            if (empty($scopeId)) {
                $storeId = To::int($this->request->getParam($scopeType, -1));
            } else {
                $storeId = $scopeId;
            }
            if ($storeId != -1) {
                return $this->initialiseScope($scopeType, $storeId);
            }
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to get current configuration scope");
        }
        return $this->scopeFactory->create();
    }

    /**
     * @inheirtDoc
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function initialiseScope(
        string $type,
        int $id
    ): ConfigScopeInterface {
        if (empty($type)) {
            throw new InvalidArgumentException(__("Scope type cannot be empty"));
        }
        $scope = $this->scopeFactory->create();
        $scope->setId($id);
        $scope->setType($type);
        switch ($type) {
            case ScopeInterface::SCOPE_WEBSITE:
                $scope->setWebsiteId($id);
                $website = $this->storeManager->getWebsite($id);
                $scope->setName($website->getName());
                $code = (string)$website->getCode();
                $scope->setCode($code);
                $scope->setWebsiteCode($code);
                $scope->setIsExplicitlyConnected(!empty($this->configReader->getAPIKey($type, $id)));
                $baseURL = (string)$this->urlInterface->getBaseUrl(["_secure" => true]);
                if (empty($baseURL)) {
                    throw new InvalidArgumentException(__("Website base URL cannot be empty"));
                }
                $scope->setBaseURL(rtrim($baseURL, '/'));
                $stores = $this->storeManager->getStores();
                foreach ($stores as $store) {
                    if (To::int($store->getWebsiteId()) === $id) {
                        $scope->addStoreId(To::int($store->getId()));
                    }
                }
                break;
            case ScopeInterface::SCOPE_STORE:
                /** @var Store $store */
                $store = $this->storeManager->getStore($id);
                $websiteId = To::int($store->getWebsiteId());
                $website = $this->storeManager->getWebsite($id);
                $scope->setWebsiteId($websiteId);
                $scope->setWebsiteCode((string)$website->getCode());
                $websiteAPIKey = $this->configReader->getAPIKey(ScopeInterface::SCOPE_WEBSITE, $websiteId);
                $storeAPIKey = $this->configReader->getAPIKey($type, $id);
                $scope->setIsExplicitlyConnected(!empty($storeAPIKey));
                $scope->setName($store->getName());
                $baseURL = (string)$store->getBaseUrl(UrlInterface::URL_TYPE_LINK, true);
                if (empty($baseURL)) {
                    throw new InvalidArgumentException(__("Store base URL cannot be empty"));
                }
                $scope->setBaseURL(rtrim($baseURL, "/"));
                $scope->setCode($store->getCode());
                $scope->addStoreId($id);
                $scope->setParent($this->initialiseScope(ScopeInterface::SCOPE_WEBSITE, $websiteId));
                break;
            default:
                throw new InvalidArgumentException(new Phrase("Unsupported scope type $type"));
        }

        return $scope;
    }
}
