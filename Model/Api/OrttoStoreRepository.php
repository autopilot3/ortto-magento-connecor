<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Api;


use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\OrttoStoreRepositoryInterface;
use Ortto\Connector\Logger\OrttoLogger;
use Ortto\Connector\Model\Data\OrttoStoreFactory;

class OrttoStoreRepository implements OrttoStoreRepositoryInterface
{
    private OrttoStoreFactory $storeFactory;
    private StoreManagerInterface $storeManager;
    private OrttoLogger $logger;

    public function __construct(
        OrttoLogger $logger,
        OrttoStoreFactory $storeFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->storeFactory = $storeFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getById(ConfigScopeInterface $scope, int $storeId)
    {
        if ($storeId == $scope->getId()) {
            // Store details will be set to current scope's
            return null;
        }
        try {
            $orttoStore = $this->storeFactory->create();
            $store = $this->storeManager->getStore($storeId);
            $orttoStore->setId($storeId);
            $orttoStore->setName($store->getName());
            $url = (string)$store->getBaseUrl(UrlInterface::URL_TYPE_LINK, true);
            $orttoStore->setUrl(rtrim($url, "/"));
        } catch (NoSuchEntityException $e) {
            // This should never happen. In case store has been removed!
            $this->logger->error($e, sprintf("Cannot find store by ID %d", $storeId));
            return null;
        }

        return $orttoStore;
    }
}
