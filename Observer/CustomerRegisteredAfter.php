<?php
declare(strict_types=1);

namespace Ortto\Connector\Observer;

use Magento\Store\Model\ScopeInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Api\OrttoClientInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Exception;

class CustomerRegisteredAfter implements ObserverInterface
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
            /** @var CustomerInterface $customer */
            $customer = $event->getData('customer');
            $storeId = To::int($customer->getStoreId());
            $scope = $this->scopeManager->initialiseScope(ScopeInterface::SCOPE_STORE, $storeId);
            if (!$this->helper->shouldExportCustomer($scope, $customer)) {
                return;
            }
            $this->orttoClient->importContacts($scope, [$customer], true);
        } catch (Exception $e) {
            $this->logger->error($e, 'CustomerRegisteredAfter: Failed to export the customer');
        }
    }
}
