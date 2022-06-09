<?php
declare(strict_types=1);

namespace Ortto\Connector\Observer;

use Magento\Store\Model\ScopeInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Api\OrttoClientInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Exception;

class CustomerAddressSavedAfterFrontend implements ObserverInterface
{
    private OrttoLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;
    private OrttoClientInterface $orttoClient;
    private CustomerRepository $customerRepository;
    private Data $helper;

    public function __construct(
        OrttoLoggerInterface $logger,
        ScopeManagerInterface $scopeManager,
        OrttoClientInterface $orttoClient,
        CustomerRepository $customerRepository,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->scopeManager = $scopeManager;
        $this->orttoClient = $orttoClient;
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            /** @var AddressInterface $address */
            $address = $event->getData("customer_address");
            if (empty($address)) {
                $this->logger->warn("Customer address was not provided");
                return;
            }
            $customer = $this->customerRepository->getById(To::int($address->getCustomerId()));
            $storeId = To::int($customer->getStoreId());
            $scope = $this->scopeManager->initialiseScope(ScopeInterface::SCOPE_STORE, $storeId);
            if (!$this->helper->shouldExportCustomer($scope, $customer)) {
                return;
            }
            $this->orttoClient->importContacts($scope, [$customer]);
        } catch (Exception $e) {
            $this->logger->error($e, 'CustomerAddressSavedAfterFrontend: Failed to export the customer');
        }
    }
}
