<?php
declare(strict_types=1);

namespace Ortto\Connector\Observer;

use Magento\Catalog\Model\Product;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Api\OrttoClientInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Exception;

class ProductSavedAfter implements ObserverInterface
{
    private OrttoLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;
    private OrttoClientInterface $orttoClient;
    private Data $helper;

    public function __construct(
        OrttoLoggerInterface $logger,
        ScopeManagerInterface $scopeManager,
        OrttoClientInterface $orttoClient,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->scopeManager = $scopeManager;
        $this->orttoClient = $orttoClient;
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            /** @var Product $product */
            $product = $event->getData('product');
            $this->logger->debug("Product Created/Updated", ["sku" => $product->getSku(), 'id' => $product->getId()]);
            $scopes = $this->scopeManager->getActiveScopes();
            foreach ($scopes as $scope) {
                if (!$this->helper->shouldExportProduct($scope, $product)) {
                    continue;
                }
                $this->orttoClient->importProducts($scope, [$product]);
            }
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to export product");
        }
    }
}
