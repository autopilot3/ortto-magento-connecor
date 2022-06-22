<?php
declare(strict_types=1);

namespace Ortto\Connector\Observer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Address;
use Magento\Store\Model\ScopeInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Api\OrttoClientInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
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
    private AddressRepositoryInterface $addressRepository;

    public function __construct(
        OrttoLoggerInterface $logger,
        ScopeManagerInterface $scopeManager,
        OrttoClientInterface $orttoClient,
        CustomerRepository $customerRepository,
        AddressRepositoryInterface $addressRepository,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->scopeManager = $scopeManager;
        $this->orttoClient = $orttoClient;
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
        $this->addressRepository = $addressRepository;
    }

    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            /** @var Address $address */
            $address = $event->getData("customer_address");
            if (empty($address)) {
                $this->logger->warn("Customer address was not provided");
                return;
            }

            $customer = $this->customerRepository->getById(To::int($address->getCustomerId()));

            $customerAddresses = $customer->getAddresses();
            $addressId = To::int($address->getEntityId());
            if (empty($customerAddresses)) {
                // The first time customer sets their address via profile,
                // the address is not associated with the customer yet!!
                $customer->setAddresses([$address->getDataModel($addressId, $addressId)]);
            } else {
                $updatedAddress = $this->addressRepository->getById($addressId);
                $addresses = [];
                foreach ($customerAddresses as $customerAddress) {
                    if (To::int($customerAddress->getId()) == $addressId) {
                        $addresses[] = $updatedAddress;
                    } else {
                        $addresses[] = $customerAddress;
                    }
                }
                $customer->setAddresses($addresses);
            }
            $storeId = To::int($customer->getStoreId());
            $scope = $this->scopeManager->initialiseScope(ScopeInterface::SCOPE_STORE, $storeId);
            if (!$this->helper->shouldExportCustomer($scope, $customer)) {
                return;
            }
            $this->orttoClient->importContacts($scope, [$customer], true);
        } catch (Exception $e) {
            $this->logger->error($e, 'CustomerAddressSavedAfterFrontend: Failed to export the customer');
        }
    }
}
