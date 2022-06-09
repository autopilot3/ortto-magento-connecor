<?php
declare(strict_types=1);

namespace Ortto\Connector\Service;

use Ortto\Connector\Api\Data\TrackingDataInterface;
use Ortto\Connector\Api\Data\TrackingDataInterfaceFactory;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Helper\To;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class TrackDataProvider implements TrackDataProviderInterface
{
    private StoreManagerInterface $storeManager;
    private Session $session;
    private TrackingDataInterfaceFactory $factory;
    private ScopeManagerInterface $scopeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        Session $session,
        TrackingDataInterfaceFactory $factory,
        ScopeManagerInterface $scopeManager
    ) {
        $this->storeManager = $storeManager;
        $this->session = $session;
        $this->factory = $factory;
        $this->scopeManager = $scopeManager;
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
        $data->setScope($scope);
        if ($this->session->isLoggedIn()) {
            $customer = $this->session->getCustomer();
            $data->setEmail($customer->getEmail());
            $data->setPhone($this->getCustomerPhoneNumber($customer));
        }
        return $data;
    }

    /**
     * @param Customer $customer
     * @return string
     */
    private function getCustomerPhoneNumber($customer): string
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
