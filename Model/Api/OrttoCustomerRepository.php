<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Api;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\Subscriber;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\OrttoCustomerRepositoryInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Ortto\Connector\Model\Data\OrttoCustomerFactory;
use Ortto\Connector\Model\Data\ListCustomerResponseFactory;
use Ortto\Connector\Model\Data\OrttoAddressFactory;
use Ortto\Connector\Model\Data\OrttoCountryFactory;
use Zend_Db_Select;

class OrttoCustomerRepository implements OrttoCustomerRepositoryInterface
{
    private Data $helper;
    private OrttoLogger $logger;
    private ListCustomerResponseFactory $listResponseFactory;
    private CollectionFactory $customerCollection;
    private OrttoCustomerFactory $customerFactory;
    private GroupRepositoryInterface $groupRepository;
    private Subscriber $subscriber;
    private OrttoAddressFactory $addressFactory;
    private OrttoCountryFactory $countryFactory;
    private CountryInformationAcquirerInterface $countryRepository;

    public function __construct(
        Data $helper,
        OrttoLogger $logger,
        CollectionFactory $customerCollection,
        ListCustomerResponseFactory $listResponseFactory,
        OrttoCustomerFactory $customerFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Ortto\Connector\Model\Data\OrttoAddressFactory $addressFactory,
        \Ortto\Connector\Model\Data\OrttoCountryFactory $countryFactory,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryRepository
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->listResponseFactory = $listResponseFactory;
        $this->customerCollection = $customerCollection;
        $this->customerFactory = $customerFactory;
        $this->groupRepository = $groupRepository;
        $this->subscriber = $subscriber;
        $this->addressFactory = $addressFactory;
        $this->countryFactory = $countryFactory;
        $this->countryRepository = $countryRepository;
    }

    /** @inheirtDoc */
    public function getList(ConfigScopeInterface $scope, int $page, string $checkpoint, int $pageSize, array $data = [])
    {
        if ($page < 1) {
            $page = 1;
        }
        if ($pageSize == 0) {
            $pageSize = 100;
        }

        $addressColumns = [
            'address_entity_id' => 'a.entity_id',
            'address_city' => 'a.' . AddressInterface::CITY,
            'address_country_id' => 'a.' . AddressInterface::COUNTRY_ID,
            'address_fax' => 'a.' . AddressInterface::FAX,
            'address_first_name' => 'a.' . AddressInterface::FIRSTNAME,
            'address_last_name' => 'a.' . AddressInterface::LASTNAME,
            'address_middle_name' => 'a.' . AddressInterface::MIDDLENAME,
            'address_postcode' => 'a.' . AddressInterface::POSTCODE,
            'address_prefix' => 'a.' . AddressInterface::PREFIX,
            'address_suffix' => 'a.' . AddressInterface::SUFFIX,
            'address_region' => 'a.' . AddressInterface::REGION,
            'address_street' => 'a.' . AddressInterface::STREET,
            'address_telephone' => 'a.' . AddressInterface::TELEPHONE,
            'address_company' => 'a.' . AddressInterface::COMPANY,
            'address_vat_id' => 'a.' . AddressInterface::VAT_ID,
        ];

        $customerColumns = [
            // Since we are doing left join, there will be duplicate in the result set
            // $collection->getItems() will fail because it is using entity_id as array index
            'customer_entity_id' => 'entity_id',
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

        $collection = $this->customerCollection->create();
        $connection = $collection->getConnection();
        $addressTable = $connection->getTableName('customer_address_entity');
        $query = $collection->setPage($page, $pageSize)
            ->getSelect()
            // To avoid `getItems` call to fail because of duplicates
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns($customerColumns)
            ->order('e.entity_id DESC')
            ->joinLeft(
                ['a' => $addressTable],
                'e.entity_id = a.parent_id',
                $addressColumns
            );

        if (!empty($checkpoint)) {
            $query->where('e.updated_at > ?', $checkpoint)
                ->orWhere('a.updated_at > ?', $checkpoint);
        }

        $result = $this->listResponseFactory->create();
        $total = To::int($collection->getSize());
        $result->setTotal($total);
        if ($total == 0) {
            return $result;
        }
        $customers = [];
        $lastCustomerID = -1;
        $customerGroup = [];
        foreach ($collection->getItems() as $customer) {
            $customerId = To::int($customer->getData('customer_entity_id'));
            if ($lastCustomerID != -1 && $lastCustomerID != $customerId) {
                $customers[] = $this->merge($lastCustomerID, $customerGroup);
                $customerGroup = [];
            }
            $customerGroup[] = $customer;
            $lastCustomerID = $customerId;
        }
        if (!empty($customerGroup)) {
            $customers[] = $this->merge($lastCustomerID, $customerGroup);
        }
        $result->setCustomers($customers);
        $result->setHasMore($page < $total / $pageSize);

        return $result;
    }

    /**
     * @param int $customerId
     * @param DataObject[] $customers
     * @return \Ortto\Connector\Api\Data\OrttoCustomerInterface
     */
    private function merge(int $customerId, array $customers)
    {
        $data = $this->customerFactory->create();
        $phoneNumber = '';
        $phoneSetToBilling = false;
        foreach ($customers as $index => $customer) {
            if ($index == 0) {
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
                $data->setCreatedIn((string)$customer->getData(CustomerInterface::CREATED_IN));
                if ($groupId = $customer->getData(CustomerInterface::GROUP_ID)) {
                    try {
                        if ($group = $this->groupRepository->getById($groupId)) {
                            $data->setGroup(($group->getCode()));
                        }
                    } catch (NoSuchEntityException|LocalizedException $e) {
                        $this->logger->error($e, 'Failed to fetch customer group details');
                    }
                }
                $sub = $this->subscriber->loadByCustomer(
                    $customerId,
                    To::int($customer->getData(CustomerInterface::WEBSITE_ID))
                );
                $data->setIsSubscribed($sub->isSubscribed());
            }
            $addressId = $customer->getData('address_entity_id');
            if (empty($addressId)) {
                continue;
            }
            $this->logger->info("EMP", [
                To::int($addressId),
                To::int($customer->getData(CustomerInterface::DEFAULT_BILLING)),
                To::int($customer->getData(CustomerInterface::DEFAULT_SHIPPING)),
            ]);

            switch (To::int($addressId)) {
                case To::int($customer->getData(CustomerInterface::DEFAULT_BILLING)):
                    $address = $this->extractAddress($customer);
                    if ($phone = $address->getPhone()) {
                        $phoneNumber = $phone;
                        $phoneSetToBilling = true;
                    }
                    $data->setBillingAddress($address);
                    break;
                case To::int($customer->getData(CustomerInterface::DEFAULT_SHIPPING)):
                    $address = $this->extractAddress($customer);
                    // Billing phone number takes precedence
                    if (!$phoneSetToBilling) {
                        if ($phone = $address->getPhone()) {
                            $phoneNumber = $phone;
                        }
                    }
                    $data->setShippingAddress($address);
                    break;
                default:
                    if (empty($phoneNumber)) {
                        $phoneNumber = (string)$customer->getData('address_telephone');
                    }
                    break;
            }
        }

        $data->setPhone($phoneNumber);
        return $data;
    }

    /**
     * @param DataObject $customer
     * @return \Ortto\Connector\Api\Data\OrttoAddressInterface
     */
    private function extractAddress($customer)
    {
        $data = $this->addressFactory->create();
        $data->setCity((string)$customer->getData('address_city'));
        $data->setCompany((string)$customer->getData('address_company'));
        $data->setFirstName((string)$customer->getData('address_first_name'));
        $data->setLastName((string)$customer->getData('address_last_name'));
        $data->setMiddleName((string)$customer->getData('address_middle_name'));
        $data->setPostCode((string)$customer->getData('address_postcode'));
        $data->setPrefix((string)$customer->getData('address_prefix'));
        $data->setSuffix((string)$customer->getData('address_suffix'));
        $data->setRegion((string)$customer->getData('address_region'));
        $data->setVat((string)$customer->getData('address_vat_id'));
        $data->setPhone((string)$customer->getData('address_telephone'));
        $data->setFax((string)$customer->getData('address_fax'));
        if ($street = $customer->getData('address_street')) {
            $data->setStreetLines(explode("\n", $street));
        }
        $data->setCountry($this->extractCountry($customer));
        return $data;
    }

    /**
     * @param DataObject $customer
     * @return \Ortto\Connector\Api\Data\OrttoCountryInterface
     */
    private function extractCountry($customer)
    {
        $data = $this->countryFactory->create();
        $countryId = (string)$customer->getData('address_country_id');
        try {
            if ($country = $this->countryRepository->getCountryInfo($countryId)) {
                $data->setAbbr2((string)$country->getTwoLetterAbbreviation());
                $data->setAbbr3((string)$country->getThreeLetterAbbreviation());
                $data->setNameEn((string)$country->getFullNameEnglish());
                $data->setNameLocal((string)$country->getFullNameLocale());
            }
        } catch (NoSuchEntityException $e) {
            $data->setAbbr2($countryId);
            $this->logger->debug('Failed to fetch country details: ' . $e->getMessage());
        }
        return $data;
    }
}
