<?php
declare(strict_types=1);


namespace Ortto\Connector\Plugin;

use Magento\ProductAlert\Model\Stock;
use Magento\Store\Model\ScopeInterface;
use Ortto\Connector\Api\OrttoClientInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;

class StockAlertPlugin
{
    private OrttoLoggerInterface $logger;
    private OrttoClientInterface $orttoClient;
    private ScopeManagerInterface $scopeManager;
    private Data $helper;

    public function __construct(
        OrttoLoggerInterface $logger,
        OrttoClientInterface $orttoClient,
        ScopeManagerInterface $scopeManager,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->orttoClient = $orttoClient;
        $this->scopeManager = $scopeManager;
        $this->helper = $helper;
    }

    public function afterSave(Stock $model): Stock
    {
        try {
            $scope = $this->scopeManager->initialiseScope(ScopeInterface::SCOPE_STORE, To::int($model->getStoreId()));
            if (!$this->helper->shouldExportStockAlert($scope, $model)) {
                return $model;
            }
            $this->orttoClient->importProductStockAlerts($scope, [$model]);
        } catch (\Exception $e) {
            $this->logger->error($e, "Failed to export product stock alert");
        }
        return $model;
    }
}
