<?php
declare(strict_types=1);


namespace Ortto\Connector\Plugin;

use Magento\ProductAlert\Model\Stock;
use Ortto\Connector\Api\OrttoClientInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Helper\Data;
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
            $scopes = $this->scopeManager->getActiveScopes();
            foreach ($scopes as $scope) {
                if (!$this->helper->shouldExportStockAlert($scope, $model)) {
                    continue;
                }
                $this->orttoClient->importProductStockAlerts($scope, [$model]);
            }
        } catch (\Exception $e) {
            $this->logger->error($e, "Failed to export product stock alert");
        }
        return $model;
    }
}
