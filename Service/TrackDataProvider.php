<?php
declare(strict_types=1);

namespace Ortto\Connector\Service;

use Magento\Framework\App\Http\Context;
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
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\SessionFactory;

class TrackDataProvider implements TrackDataProviderInterface
{
    private StoreManagerInterface $storeManager;
    private TrackingDataInterfaceFactory $factory;
    private ScopeManagerInterface $scopeManager;
    private Context $httpContext;
    private OrttoLogger $logger;

    private Session $session;
    private ConfigurationReaderInterface $configReader;
    private SessionFactory $sessionFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        TrackingDataInterfaceFactory $factory,
        ScopeManagerInterface $scopeManager,
        ConfigurationReaderInterface $configReader,
        Context $httpContext,
        OrttoLogger $logger,
        Session $session,
        SessionFactory $sessionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->factory = $factory;
        $this->scopeManager = $scopeManager;
        $this->httpContext = $httpContext;
        $this->logger = $logger;
        $this->configReader = $configReader;
        $this->session = $session;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getData(): TrackingDataInterface
    {
        $customerSession = $this->sessionFactory->create();
        if ($customerSession->isLoggedIn()) {
            // it works now
            $this->logger->info("Session Factory", ['e' => $customerSession->getCustomer()->getEmail()]);
        }
        $store = $this->storeManager->getStore();
        $storeId = To::int($store->getId());
        $scope = $this->scopeManager->initialiseScope(ScopeInterface::SCOPE_STORE, $storeId);
        $data = $this->factory->create();
        $data->setEnabled($scope->isConnected() && $this->configReader->isTrackingEnabled($scope->getType(),
                $scope->getId()));
        if (!$data->isTrackingEnabled()) {
            $this->logger->info("Tracking disabled", $scope->toArray());
            return $data;
        }
        $data->setScope($scope);
        $customerData = $this->getCustomerData();
        $this->logger->info("Customer Data", $customerData);
        $data->setEmail($customerData[TrackingDataInterface::EMAIL]);
        $data->setPhone($customerData[TrackingDataInterface::PHONE]);
        return $data;
    }

    /**
     * @return array
     */
    private function getCustomerData(): array
    {
        $value = $this->httpContext->getValue(self::CUSTOMER_ID_SESSION_KEY);
        if (!empty($value)) {
            $this->logger->info("HTTP Context");
            return [
                TrackingDataInterface::EMAIL => $this->getTextValue(self::CUSTOMER_EMAIL_SESSION_KEY),
                TrackingDataInterface::PHONE => $this->getTextValue(self::CUSTOMER_PHONE_SESSION_KEY),
            ];
        }
        $this->logger->info("Checking Session Fallback");
        // Fallback to session for AJAX calls (eg. Added to cart activity)
        if ($this->session->isLoggedIn()) {
            $this->logger->info("Session is Logged in");
            $customer = $this->session->getCustomer();
            $email = $customer->getEmail();
            return [
                TrackingDataInterface::EMAIL => $email == null ? '' : $email,
                TrackingDataInterface::PHONE => $this->getCustomerPhoneNumber($customer),
            ];
        }
        $this->logger->info("Not Logged in");
        return [
            TrackingDataInterface::EMAIL => '',
            TrackingDataInterface::PHONE => '',
        ];
    }

    private function getTextValue(string $key)
    {
        $value = $this->httpContext->getValue($key);
        return $value ? (string)$value : '';
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
