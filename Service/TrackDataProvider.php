<?php
declare(strict_types=1);

namespace Ortto\Connector\Service;

use Ortto\Connector\Api\ConfigurationReaderInterface;
use Ortto\Connector\Api\Data\TrackingDataInterface;
use Ortto\Connector\Api\Data\TrackingDataInterfaceFactory;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Helper\To;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\SessionFactory;

class TrackDataProvider implements TrackDataProviderInterface
{
    private StoreManagerInterface $storeManager;
    private TrackingDataInterfaceFactory $factory;
    private ScopeManagerInterface $scopeManager;
    private OrttoLogger $logger;

    private ConfigurationReaderInterface $configReader;
    private SessionFactory $sessionFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        TrackingDataInterfaceFactory $factory,
        ScopeManagerInterface $scopeManager,
        ConfigurationReaderInterface $configReader,
        OrttoLogger $logger,
        SessionFactory $sessionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->factory = $factory;
        $this->scopeManager = $scopeManager;
        $this->logger = $logger;
        $this->configReader = $configReader;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getData(): TrackingDataInterface
    {
        $store = $this->storeManager->getStore();
        $storeId = To::int($store->getId());
        $scope = $this->scopeManager->initialiseScope(ScopeInterface::SCOPE_STORE, $storeId);
        $data = $this->factory->create();
        $data->setEnabled($scope->isConnected() && $this->configReader->isTrackingEnabled($scope->getType(),
                $scope->getId()));
        if (!$data->isTrackingEnabled()) {
            $this->logger->debug("Tracking is disabled", $scope->toArray());
            return $data;
        }
        $data->setScope($scope);
        $customerSession = $this->sessionFactory->create();
        if ($customerSession->isLoggedIn()) {
            $customer = $customerSession->getCustomer();
            if ($email = $customer->getEmail()) {
                $data->setEmail($email);
            }
            if ($phone = $this->getCustomerPhoneNumber($customer)) {
                $data->setPhone($phone);
            }
            $this->logger->debug("Authenticated user session loaded",
                ['email' => $data->getEmail(), 'phone' => $data->getPhone()]);
        }
        return $data;
    }

    /**
     * @param Customer $customer
     * @return string
     */
    private function getCustomerPhoneNumber(Customer $customer): string
    {
        $addresses = $customer->getAddresses();
        if (empty($addresses)) {
            return '';
        }

        $shipping = $customer->getDefaultShippingAddress();
        $billing = $customer->getDefaultBillingAddress();

        $phone = '';
        foreach ($addresses as $address) {
            if ($address instanceof Address) {
                switch (true) {
                    case $billing && $billing->getEntityId() == $address->getEntityId():
                        $billingPhone = $address->getTelephone();
                        if (!empty($billingPhone)) {
                            // Billing phone number takes precedence
                            return $billingPhone;
                        }
                        break;
                    case $shipping && $shipping->getEntityId() == $address->getEntityId():
                        $shippingPhone = $address->getTelephone();
                        if (!empty($shippingPhone)) {
                            // Shipping phone overrides other addresses' phone number (except billing)
                            $phone = $shippingPhone;
                        }
                        break;
                    default:
                        if (empty($phone)) {
                            $phone = $address->getTelephone();
                        }
                }
            }
        }

        return $phone;
    }
}
