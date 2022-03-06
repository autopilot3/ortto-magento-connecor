<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Service;

use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\ConfigurationReaderInterface;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\Scope;
use Autopilot\AP3Connector\Model\ScopeFactory;
use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ScopeManager implements ScopeManagerInterface
{
    private StoreManagerInterface $storeManager;
    private EncryptorInterface $encryptor;
    private AutopilotLoggerInterface $logger;
    private ConfigurationReaderInterface $configReader;
    private ScopeFactory $scopeFactory;
    private RequestInterface $request;

    public function __construct(
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        AutopilotLoggerInterface $logger,
        ConfigurationReaderInterface $configReader,
        ScopeFactory $scopeFactory,
        RequestInterface $request
    ) {
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        $this->configReader = $configReader;
        $this->scopeFactory = $scopeFactory;
        $this->request = $request;
    }

    public function getActiveScopes(): array
    {
        $result = [];

        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            try {
                $scope = $this->initialiseScope(ScopeInterface::SCOPE_WEBSITE, (int)$website->getId(), $websites);
                if ($scope->isConnected()) {
                    $result[] = $scope;
                }
            } catch (Exception $e) {
                $this->logger->error($e, "Failed to initialise website scope");
            }
        }

        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            try {
                $scope = $this->initialiseScope(ScopeInterface::SCOPE_STORE, (int)$store->getId(), $websites, $stores);
                if ($scope->isConnected()) {
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
                    $websiteId = (int)$this->request->getParam($scopeType, -1);
                } else {
                    $websiteId = $scopeId;
                }
                if ($websiteId != -1) {
                    return $this->initialiseScope($scopeType, $websiteId);
                }
            }

            $scopeType = ScopeInterface::SCOPE_STORE;
            if (empty($scopeId)) {
                $storeId = (int)$this->request->getParam($scopeType, -1);
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
     * @param string $type
     * @param int $id
     * @param WebsiteInterface[] $websites
     * @param StoreInterface[] $stores
     * @return ConfigScopeInterface
     * @throws InvalidArgumentException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws NotFoundException
     */
    public function initialiseScope(
        string $type,
        int $id,
        array $websites = [],
        array $stores = []
    ): ConfigScopeInterface {
        $scope = $this->scopeFactory->create();
        $scope->setId($id);
        $scope->setType($type);
        if (empty($stores)) {
            $stores = $this->storeManager->getStores();
        }
        switch ($type) {
            case ScopeInterface::SCOPE_WEBSITE:
                $scope->setWebsiteId($id);
                $website = $this->storeManager->getWebsite($id);
                $scope->setName($website->getName());
                $code = $website->getCode();
                if (empty($websites)) {
                    $websites = $this->storeManager->getWebsites();
                }
                $scope->setIsConnected(!empty($this->configReader->getAPIKey($type, $id)));
                $count = 0;
                foreach ($websites as $w) {
                    if ($w->getCode() === $code) {
                        $count++;
                    }
                }
                foreach ($stores as $store) {
                    if ((int)$store->getWebsiteId() === $id) {
                        $scope->addStoreId((int)$store->getId());
                    }
                }
                break;
            case ScopeInterface::SCOPE_STORE:
                $store = $this->storeManager->getStore($id);
                $websiteId = (int)$store->getWebsiteId();
                $scope->setWebsiteId($websiteId);
                $websiteAPIKey = $this->configReader->getAPIKey(ScopeInterface::SCOPE_WEBSITE, $websiteId);
                $storeAPIKey = $this->configReader->getAPIKey($type, $id);
                $scope->setIsConnected($websiteAPIKey !== $storeAPIKey && !empty($storeAPIKey));
                $scope->setName($store->getName());
                $code = $store->getCode();
                $scope->addStoreId($id);
                $count = 0;
                foreach ($stores as $store) {
                    if ($store->getCode() === $code) {
                        $count++;
                    }
                }
                break;
            default:
                throw new InvalidArgumentException(new Phrase("Unsupported scope type $type"));
        }

        if (empty(trim($code))) {
            throw new NotFoundException(new Phrase("Scope not found", ['type' => $type, 'id' => $id]));
        }

        // Code is not necessarily unique in Magento
        if ($count > 1) {
            $scope->setCode(sprintf('$%s_%d', $code, $id));
        } else {
            $scope->setCode($code);
        }

        return $scope;
    }
}
