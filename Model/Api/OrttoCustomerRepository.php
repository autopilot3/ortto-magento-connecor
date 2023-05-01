<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Api;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory as QuoteAddressCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\Data\ListCustomerResponseInterface;
use Ortto\Connector\Api\OrttoCustomerRepositoryInterface;
use Ortto\Connector\Api\OrttoSubscriberRepositoryInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use \Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Ortto\Connector\Model\Data\OrttoCustomerFactory;
use Ortto\Connector\Model\Data\ListCustomerResponseFactory;
use Ortto\Connector\Model\Data\OrttoAddressFactory;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;

class OrttoCustomerRepository implements OrttoCustomerRepositoryInterface
{
    private array $customerColumnsToSelect = [
        self::ENTITY_ID,
        CustomerInterface::PREFIX,
        CustomerInterface::FIRSTNAME,
        CustomerInterface::MIDDLENAME,
        CustomerInterface::LASTNAME,
        CustomerInterface::SUFFIX,
        CustomerInterface::EMAIL,
        CustomerInterface::CREATED_AT,
        CustomerInterface::UPDATED_AT,
        CustomerInterface::CREATED_IN,
        CustomerInterface::DOB,
        CustomerInterface::GENDER,
        CustomerInterface::GROUP_ID,
        CustomerInterface::DEFAULT_BILLING,
        CustomerInterface::DEFAULT_SHIPPING,
    ];
    const ENTITY_ID = 'entity_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const STORE_ID = 'store_id';
    const QUOTE_ID = 'quote_id';
    const BILLING_ADDRESS = 'billing';
    const SHIPPING_ADDRESS = 'shipping';
    const ADDRESS_TYPE = 'address_type';
    const IS_ACTIVE = 'is_active';
    const IP_ADDRESS = 'remote_ip';
    const CUSTOMER_PREFIX = 'customer_prefix';
    const CUSTOMER_FIRST_NAME = 'customer_firstname';
    const CUSTOMER_MIDDLE_NAME = 'customer_middlename';
    const CUSTOMER_LAST_NAME = 'customer_lastname';
    const CUSTOMER_SUFFIX = 'customer_suffix';
    const CUSTOMER_EMAIL = 'customer_email';
    const CUSTOMER_DOB = 'customer_dob';
    const CUSTOMER_GENDER = 'customer_gender';
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    const CUSTOMER_IS_GUEST = 'customer_is_guest';

    private array $countryCache;
    private Data $helper;
    private OrttoLogger $logger;
    private ListCustomerResponseFactory $listResponseFactory;
    private CustomerCollectionFactory $customerCollection;
    private OrttoCustomerFactory $customerFactory;
    private OrttoAddressFactory $addressFactory;
    private AddressCollectionFactory $addressCollection;
    private QuoteCollectionFactory $quoteCollection;
    private QuoteAddressCollectionFactory $quoteAddressCollection;
    private CountryFactory $countryFactory;
    private OrttoSubscriberRepositoryInterface $subscriberRepository;

    public function __construct(
        Data $helper,
        OrttoLogger $logger,
        CustomerCollectionFactory $customerCollection,
        AddressCollectionFactory $addressCollection,
        QuoteCollectionFactory $quoteCollection,
        QuoteAddressCollectionFactory $quoteAddressCollection,
        ListCustomerResponseFactory $listResponseFactory,
        OrttoCustomerFactory $customerFactory,
        \Ortto\Connector\Model\Data\OrttoAddressFactory $addressFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        OrttoSubscriberRepositoryInterface $subscriberRepository
    ) {
        $this->countryCache = [];
        $this->helper = $helper;
        $this->logger = $logger;
        $this->listResponseFactory = $listResponseFactory;
        $this->customerCollection = $customerCollection;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->addressCollection = $addressCollection;
        $this->quoteCollection = $quoteCollection;
        $this->quoteAddressCollection = $quoteAddressCollection;
        $this->countryFactory = $countryFactory;
        $this->subscriberRepository = $subscriberRepository;
    }

    /** @inheirtDoc
     * @throws LocalizedException
     */
    public function getList(
        ConfigScopeInterface $scope,
        bool $newsletter,
        bool $crossStore,
        int $page,
        string $checkpoint,
        int $pageSize,
        array $data = []
    ) {
        if ($page < 1) {
            $page = 1;
        }
        if ($pageSize == 0) {
            $pageSize = 100;
        }

        if (array_key_exists(self::ANONYMOUS, $data) && To::bool($data[self::ANONYMOUS])) {
            return $this->getAnonymousCustomerList($scope, $newsletter, $crossStore, $page, $checkpoint, $pageSize);
        }
        return $this->getCustomersList($scope, $newsletter, $crossStore, $page, $checkpoint, $pageSize);
    }

    /** @inheirtDoc
     * @throws LocalizedException
     */
    public function getByIds(
        ConfigScopeInterface $scope,
        bool $newsletter,
        bool $crossStore,
        array $customerIds,
        array $data = []
    ) {
        $result = $this->listResponseFactory->create();
        $customerIds = array_unique($customerIds, SORT_NUMERIC);
        if (empty($customerIds)) {
            return $result;
        }
        $customerCollection = $this->customerCollection->create();
        $customerCollection->addAttributeToSelect($this->customerColumnsToSelect)
            ->addFieldToFilter(self::ENTITY_ID, ['in' => $customerIds]);

        $total = To::int($customerCollection->getSize());
        $result->setTotal($total);
        if ($total == 0) {
            return $result;
        }

        $addressIds = [];
        /** @var DataObject[] $customersData */
        $customersData = [];
        /** @var string[] $emails */
        $emails = [];
        foreach ($customerCollection->getItems() as $customer) {
            $customersData[] = $customer;
            if ($addressId = $customer->getData(CustomerInterface::DEFAULT_SHIPPING)) {
                $addressIds[] = To::int($addressId);
            }
            if ($addressId = $customer->getData(CustomerInterface::DEFAULT_BILLING)) {
                $addressIds[] = To::int($addressId);
            }
            if ($newsletter) {
                $email = (string)$customer->getData(CustomerInterface::EMAIL);
                if (!empty($email)) {
                    $customerId = To::int($customer->getData(self::ENTITY_ID));
                    $emails[$customerId] = $email;
                }
            }
        }

        $addresses = $this->getAddressesById($addressIds);
        $customers = [];
        foreach ($customerIds as $customerId) {
            // The make sure all the keys always exist in the result array, even if the requested
            // customer was not found!
            $customers[$customerId] = null;
        }

        /** @var bool[] $subscriptions */
        $subscriptions = [];
        if ($newsletter) {
            $subscriptions = $this->subscriberRepository->getStateByEmailAddresses($scope, $crossStore, $emails);
        }

        foreach ($customersData as $customer) {
            $customerId = To::int($customer->getData(self::ENTITY_ID));
            $subscribed = Config::DEFAULT_SUBSCRIPTION_STATUS;
            if ($newsletter && array_key_exists($customerId, $emails)) {
                $subscribed = $subscriptions[$emails[$customerId]];
            }
            $c = $this->convertCustomer($customer, $addresses, $subscribed);
            $customers[$customerId] = $c;
        }
        $result->setItems($customers);
        return $result;
    }

    /** @inheirtDoc
     * @throws LocalizedException
     */
    public function getById(
        ConfigScopeInterface $scope,
        bool $newsletter,
        bool $crossStore,
        int $customerId,
        array $data = []
    ) {
        $collection = $this->customerCollection->create();
        $collection->addAttributeToSelect($this->customerColumnsToSelect);
        $collection->addFieldToFilter(self::ENTITY_ID, ["eq" => $customerId]);

        $customerData = $collection->getItemById($customerId);
        if (empty($customerData)) {
            return null;
        }
        $addressIds = [];
        if ($addressId = $customerData->getData(CustomerInterface::DEFAULT_SHIPPING)) {
            $addressIds[] = To::int($addressId);
        }
        if ($addressId = $customerData->getData(CustomerInterface::DEFAULT_BILLING)) {
            $addressIds[] = To::int($addressId);
        }
        $addresses = $this->getAddressesById($addressIds);
        $subscribed = Config::DEFAULT_SUBSCRIPTION_STATUS;
        if ($newsletter) {
            $email = (string)$customerData->getData(CustomerInterface::EMAIL);
            if (!empty($email)) {
                $subscribed = $this->subscriberRepository->getStateByEmail($scope, $crossStore, $email);
            }
        }
        return $this->convertCustomer($customerData, $addresses, $subscribed);
    }

    /**
     * @param ConfigScopeInterface $scope
     * @param bool $newsletter
     * @param bool $crossStore
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @return ListCustomerResponseInterface
     */
    private function getAnonymousCustomerList(
        ConfigScopeInterface $scope,
        bool $newsletter,
        bool $crossStore,
        int $page,
        string $checkpoint,
        int $pageSize
    ) {
        $columnsToSelect = [
            self::ENTITY_ID,
            self::IP_ADDRESS,
            self::CUSTOMER_PREFIX,
            self::CUSTOMER_FIRST_NAME,
            self::CUSTOMER_MIDDLE_NAME,
            self::CUSTOMER_LAST_NAME,
            self::CUSTOMER_SUFFIX,
            self::CUSTOMER_EMAIL,
            self::CREATED_AT,
            self::UPDATED_AT,
            self::CUSTOMER_DOB,
            self::CUSTOMER_GENDER,
            self::CUSTOMER_GROUP_ID,
        ];

        $storeId = $scope->getId();

        $collection = $this->quoteCollection->create();
        $collection->setPageSize($pageSize)
            ->setCurPage($page)
            ->addFieldToSelect($columnsToSelect)
            ->addFieldToFilter(self::CUSTOMER_IS_GUEST, ['eq' => 1])
            // A quote with is_active field set to 1 is a shopping cart (no useful information has been stored yet)
            ->addFieldToFilter(self::IS_ACTIVE, ['eq' => 0])
            ->addFieldToFilter(self::STORE_ID, ['eq' => $storeId])
            ->setOrder(self::UPDATED_AT, SortOrder::SORT_ASC);

        if (!empty($checkpoint)) {
            $collection->addFieldToFilter(self::UPDATED_AT, ['gteq' => $checkpoint]);
        }

        $result = $this->listResponseFactory->create();
        $total = To::int($collection->getSize());
        $result->setTotal($total);
        if ($total == 0) {
            return $result;
        }

        $quoteIds = [];
        /** @var DataObject[] $customersData */
        $customersData = [];
        /** @var string[] $emails */
        $emails = [];
        foreach ($collection->getItems() as $customer) {
            if ($newsletter) {
                $email = (string)$customer->getData(self::CUSTOMER_EMAIL);
                if (!empty($email)) {
                    $emails[] = $email;
                }
            }
            $customersData[] = $customer;
            $quoteIds[] = To::int($customer->getData(self::ENTITY_ID));
        }

        /** @var bool[] $subscriptions */
        $subscriptions = [];
        if ($newsletter) {
            $subscriptions = $this->subscriberRepository->getStateByEmailAddresses($scope, $crossStore, $emails);
        }

        $addresses = $this->getQuoteAddressesById($quoteIds);
        $customers = [];
        foreach ($customersData as $customer) {
            $subscribed = Config::DEFAULT_SUBSCRIPTION_STATUS;
            if ($newsletter) {
                $email = (string)$customer->getData(self::CUSTOMER_EMAIL);
                if (!empty($email)) {
                    $subscribed = $subscriptions[$email];
                }
            }
            $customers[] = $this->convertAnonymousCustomer($customer, $addresses, $subscribed);
        }
        $result->setItems($customers);
        $result->setHasMore($page < $total / $pageSize);
        return $result;
    }

    /**
     * @param ConfigScopeInterface $scope
     * @param bool $newsletter
     * @param bool $crossStore
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @return ListCustomerResponseInterface
     * @throws LocalizedException
     */
    private function getCustomersList(
        ConfigScopeInterface $scope,
        bool $newsletter,
        bool $crossStore,
        int $page,
        string $checkpoint,
        int $pageSize
    ) {
        $customerCollection = $this->customerCollection->create();
        $customerCollection->setPage($page, $pageSize)
            ->addAttributeToSelect($this->customerColumnsToSelect)
            ->addFieldToFilter(CustomerInterface::WEBSITE_ID, ['eq' => $scope->getWebsiteId()])
            ->addFieldToFilter(CustomerInterface::STORE_ID, ['eq' => $scope->getId()])
            ->setOrder(CustomerInterface::UPDATED_AT, 'ASC');

        if (!empty($checkpoint)) {
            $customerCollection->addFieldToFilter(CustomerInterface::UPDATED_AT, ['gteq' => $checkpoint]);
        }

        $result = $this->listResponseFactory->create();
        $total = To::int($customerCollection->getSize());
        $result->setTotal($total);
        if ($total == 0) {
            return $result;
        }

        $addressIds = [];
        /** @var DataObject[] $customersData */
        $customersData = [];
        /** @var string[] $emails */
        $emails = [];
        foreach ($customerCollection->getItems() as $customer) {
            $customersData[] = $customer;
            if ($addressId = $customer->getData(CustomerInterface::DEFAULT_SHIPPING)) {
                $addressIds[] = To::int($addressId);
            }
            if ($addressId = $customer->getData(CustomerInterface::DEFAULT_BILLING)) {
                $addressIds[] = To::int($addressId);
            }
            if ($newsletter) {
                $email = (string)$customer->getData(CustomerInterface::EMAIL);
                if (!empty($email)) {
                    $customerId = To::int($customer->getData(self::ENTITY_ID));
                    $emails[$customerId] = $email;
                }
            }
        }

        $addresses = $this->getAddressesById($addressIds);
        $customers = [];

        /** @var bool[] $subscriptions */
        $subscriptions = [];
        if ($newsletter) {
            $subscriptions = $this->subscriberRepository->getStateByEmailAddresses($scope, $crossStore, $emails);
        }
        foreach ($customersData as $customer) {
            $customerId = To::int($customer->getData(self::ENTITY_ID));
            $subscribed = Config::DEFAULT_SUBSCRIPTION_STATUS;
            if ($newsletter && array_key_exists($customerId, $emails)) {
                $subscribed = $subscriptions[$emails[$customerId]];
            }
            $customers[] = $this->convertCustomer($customer, $addresses, $subscribed);
        }
        $result->setItems($customers);
        $result->setHasMore($page < $total / $pageSize);
        return $result;
    }

    /**
     * @param int[] $addressIds
     * @return \Ortto\Connector\Api\Data\OrttoAddressInterface[]
     * @throws LocalizedException
     */
    private function getAddressesById(array $addressIds)
    {
        if (empty($addressIds)) {
            return [];
        }
        $columnsToSelect = [
            self::ENTITY_ID,
            'parent_id',
            AddressInterface::CITY,
            AddressInterface::COUNTRY_ID,
            AddressInterface::FAX,
            AddressInterface::FIRSTNAME,
            AddressInterface::LASTNAME,
            AddressInterface::MIDDLENAME,
            AddressInterface::POSTCODE,
            AddressInterface::PREFIX,
            AddressInterface::SUFFIX,
            AddressInterface::REGION,
            AddressInterface::STREET,
            AddressInterface::TELEPHONE,
            AddressInterface::COMPANY,
            AddressInterface::VAT_ID,
        ];
        $addressIds = array_unique($addressIds, SORT_NUMERIC);
        $collection = $this->addressCollection->create();
        $collection->addAttributeToSelect($columnsToSelect)
            ->addFieldToFilter(self::ENTITY_ID, ['in' => $addressIds]);

        $addresses = [];
        foreach ($collection->getItems() as $address) {
            $addressId = To::int($address->getData(self::ENTITY_ID));
            $addresses[$addressId] = $this->convertAddress($address);
        }
        return $addresses;
    }

    /**
     * @param int[] $quoteIds
     * @return \Ortto\Connector\Api\Data\OrttoAddressInterface[][]
     */
    private function getQuoteAddressesById(array $quoteIds)
    {
        if (empty($quoteIds)) {
            return [];
        }
        $columnsToSelect = [
            self::QUOTE_ID,
            self::ADDRESS_TYPE,
            QuoteAddressInterface::KEY_CITY,
            QuoteAddressInterface::KEY_COUNTRY_ID,
            QuoteAddressInterface::KEY_FAX,
            QuoteAddressInterface::KEY_FIRSTNAME,
            QuoteAddressInterface::KEY_LASTNAME,
            QuoteAddressInterface::KEY_MIDDLENAME,
            QuoteAddressInterface::KEY_POSTCODE,
            QuoteAddressInterface::KEY_PREFIX,
            QuoteAddressInterface::KEY_SUFFIX,
            QuoteAddressInterface::KEY_REGION,
            QuoteAddressInterface::KEY_STREET,
            QuoteAddressInterface::KEY_TELEPHONE,
            QuoteAddressInterface::KEY_COMPANY,
            QuoteAddressInterface::KEY_VAT_ID,
        ];
        $quoteIds = array_unique($quoteIds, SORT_NUMERIC);
        $collection = $this->quoteAddressCollection->create();
        $collection->addFieldToSelect($columnsToSelect)
            ->addFieldToFilter(self::QUOTE_ID, ['in' => $quoteIds])
            ->addFieldToFilter(self::ADDRESS_TYPE, ['in' => [self::BILLING_ADDRESS, self::SHIPPING_ADDRESS]]);

        $addresses = [];
        foreach ($collection->getItems() as $address) {
            $quoteId = To::int($address->getData(self::QUOTE_ID));
            $addresses[$quoteId][] = $this->convertQuoteAddress($address);
        }
        return $addresses;
    }

    /**
     * @param DataObject $customer
     * @param \Ortto\Connector\Api\Data\OrttoAddressInterface[] $addresses
     * @param bool $subscribed
     * @return \Ortto\Connector\Api\Data\OrttoCustomerInterface
     */
    private function convertCustomer($customer, $addresses, $subscribed)
    {
        $data = $this->customerFactory->create();
        $customerId = To::int($customer->getData(self::ENTITY_ID));
        $data->setIsSubscribed($subscribed);
        $data->setId($customerId);
        $data->setFirstName((string)$customer->getData(CustomerInterface::FIRSTNAME));
        $data->setMiddleName((string)$customer->getData(CustomerInterface::MIDDLENAME));
        $data->setLastName((string)$customer->getData(CustomerInterface::LASTNAME));
        $data->setSuffix((string)$customer->getData(CustomerInterface::SUFFIX));
        $data->setPrefix((string)$customer->getData(CustomerInterface::PREFIX));
        $data->setGender($this->helper->getGenderLabel($customer->getData(CustomerInterface::GENDER)));
        $data->setEmail((string)$customer->getData(CustomerInterface::EMAIL));
        $data->setDateOfBirth($this->helper->toUTC($customer->getData(CustomerInterface::DOB)));
        $data->setCreatedAt($this->helper->toUTC($customer->getData(CustomerInterface::CREATED_AT)));
        $data->setUpdatedAt($this->helper->toUTC($customer->getData(CustomerInterface::UPDATED_AT)));

        $phoneNumber = '';
        if ($addressId = $customer->getData(CustomerInterface::DEFAULT_BILLING)) {
            if ($address = $addresses[To::int($addressId)]) {
                $address->setType(self::BILLING_ADDRESS);
                $data->setBillingAddress($address);
                $phoneNumber = $address->getPhone();
            }
        }
        if ($addressId = $customer->getData(CustomerInterface::DEFAULT_SHIPPING)) {
            if ($address = $addresses[To::int($addressId)]) {
                $address->setType(self::SHIPPING_ADDRESS);
                $data->setShippingAddress($address);
                // Billing phone number takes precedence
                if (empty($phoneNumber)) {
                    $phoneNumber = $address->getPhone();
                }
            }
        }

        $data->setPhone($phoneNumber);
        return $data;
    }

    /**
     * @param DataObject $customer
     * @param \Ortto\Connector\Api\Data\OrttoAddressInterface[][] $addresses
     * @param bool $subscribed
     * @return \Ortto\Connector\Api\Data\OrttoCustomerInterface
     */
    private function convertAnonymousCustomer($customer, $addresses, $subscribed)
    {
        $data = $this->customerFactory->create();
        $email = (string)$customer->getData(self::CUSTOMER_EMAIL);
        $data->setIsSubscribed($subscribed);
        $data->setId(self::ANONYMOUS_CUSTOMER_ID);
        $data->setPrefix((string)$customer->getData(self::CUSTOMER_PREFIX));
        $data->setFirstName((string)$customer->getData(self::CUSTOMER_FIRST_NAME));
        $data->setMiddleName((string)$customer->getData(self::CUSTOMER_MIDDLE_NAME));
        $data->setLastName((string)$customer->getData(self::CUSTOMER_LAST_NAME));
        $data->setSuffix((string)$customer->getData(self::CUSTOMER_SUFFIX));
        $data->setIpAddress((string)$customer->getData(self::IP_ADDRESS));
        $data->setGender($this->helper->getGenderLabel($customer->getData(self::CUSTOMER_GENDER)));
        $data->setEmail($email);
        $data->setDateOfBirth($this->helper->toUTC($customer->getData(self::CUSTOMER_DOB)));
        $data->setCreatedAt($this->helper->toUTC($customer->getData(self::CREATED_AT)));
        $data->setUpdatedAt($this->helper->toUTC($customer->getData(self::UPDATED_AT)));

        $quoteId = To::int($customer->getData(self::ENTITY_ID));
        if (empty($addresses) || !array_key_exists($quoteId, $addresses)) {
            return $data;
        }

        $phoneNumber = '';
        $quoteAddresses = $addresses[$quoteId];
        foreach ($quoteAddresses as $address) {
            if ($address->getType() == self::BILLING_ADDRESS) {
                $data->setBillingAddress($address);
                $phoneNumber = $address->getPhone();
            } else {
                $data->setShippingAddress($address);
                // Billing phone number takes precedence
                if (empty($phoneNumber)) {
                    $phoneNumber = $address->getPhone();
                }
            }
        }

        $data->setPhone($phoneNumber);
        return $data;
    }

    /**
     * @param DataObject $address
     * @return \Ortto\Connector\Api\Data\OrttoAddressInterface
     */
    private function convertAddress($address)
    {
        $data = $this->addressFactory->create();
        $data->setCity((string)$address->getData(AddressInterface::CITY));
        $data->setCompany((string)$address->getData(AddressInterface::COMPANY));
        $data->setFirstName((string)$address->getData(AddressInterface::FIRSTNAME));
        $data->setLastName((string)$address->getData(AddressInterface::LASTNAME));
        $data->setMiddleName((string)$address->getData(AddressInterface::MIDDLENAME));
        $data->setPostCode((string)$address->getData(AddressInterface::POSTCODE));
        $data->setPrefix((string)$address->getData(AddressInterface::PREFIX));
        $data->setSuffix((string)$address->getData(AddressInterface::SUFFIX));
        $data->setRegion((string)$address->getData(AddressInterface::REGION));
        $data->setVat((string)$address->getData(AddressInterface::VAT_ID));
        $data->setPhone((string)$address->getData(AddressInterface::TELEPHONE));
        $data->setFax((string)$address->getData(AddressInterface::FAX));

        $countryId = (string)$address->getData(AddressInterface::COUNTRY_ID);
        if (!empty($countryId)) {
            $data->setCountryCode($countryId);
            if (array_key_exists($countryId, $this->countryCache)) {
                $data->setCountryName($this->countryCache[$countryId]);
            } else {
                $country = $this->countryFactory->create()->loadByCode($countryId);
                if (empty($country)) {
                    // Do not look up again if we could not find it once
                    $this->countryCache[$countryId] = '';
                } else {
                    $name = (string)$country->getName();
                    $this->countryCache[$countryId] = $name;
                    $data->setCountryName($name);
                }
            }
        }

        if ($street = $address->getData(AddressInterface::STREET)) {
            $data->setStreetLines(explode("\n", $street));
        }
        return $data;
    }

    /**
     * @param DataObject $address
     * @return \Ortto\Connector\Api\Data\OrttoAddressInterface
     */
    private function convertQuoteAddress($address)
    {
        $data = $this->addressFactory->create();
        $data->setCity((string)$address->getData(QuoteAddressInterface::KEY_CITY));
        $data->setCompany((string)$address->getData(QuoteAddressInterface::KEY_COMPANY));
        $data->setFirstName((string)$address->getData(QuoteAddressInterface::KEY_FIRSTNAME));
        $data->setLastName((string)$address->getData(QuoteAddressInterface::KEY_LASTNAME));
        $data->setMiddleName((string)$address->getData(QuoteAddressInterface::KEY_MIDDLENAME));
        $data->setPostCode((string)$address->getData(QuoteAddressInterface::KEY_POSTCODE));
        $data->setPrefix((string)$address->getData(QuoteAddressInterface::KEY_PREFIX));
        $data->setSuffix((string)$address->getData(QuoteAddressInterface::KEY_SUFFIX));
        $data->setRegion((string)$address->getData(QuoteAddressInterface::KEY_REGION));
        $data->setVat((string)$address->getData(QuoteAddressInterface::KEY_VAT_ID));
        $data->setPhone((string)$address->getData(QuoteAddressInterface::KEY_TELEPHONE));
        $data->setType((string)$address->getData(self::ADDRESS_TYPE));
        $data->setFax((string)$address->getData(QuoteAddressInterface::KEY_FAX));

        $countryId = (string)$address->getData(QuoteAddressInterface::KEY_COUNTRY_ID);
        if (!empty($countryId)) {
            $data->setCountryCode($countryId);
            if (array_key_exists($countryId, $this->countryCache)) {
                $data->setCountryName($this->countryCache[$countryId]);
            } else {
                $country = $this->countryFactory->create()->loadByCode($countryId);
                if (empty($country)) {
                    // Do not look up again if we could not find it once
                    $this->countryCache[$countryId] = '';
                } else {
                    $name = (string)$country->getName();
                    $this->countryCache[$countryId] = $name;
                    $data->setCountryName($name);
                }
            }
        }

        if ($street = $address->getData(QuoteAddressInterface::KEY_STREET)) {
            $data->setStreetLines(explode("\n", $street));
        }
        return $data;
    }
}
