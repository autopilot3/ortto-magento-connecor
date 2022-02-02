<?php

namespace Autopilot\AP3Connector\Helper;

use Autopilot\AP3Connector\Logger\Logger;
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Data extends AbstractHelper
{
    const XML_PATH_BASE_URL = "autopilot/general/base_url";
    const XML_PATH_CLIENT_ID = "autopilot/general/client_id";

    // "2006-01-02T15:04:05Z07:00"
    const DATE_TIME_FORMAT = 'Y-m-d\TH:i:sP';

    private string $baseURL = "https://magento-integration-api.autopilotapp.com";
    private string $clientID = "mgqQkvCJWDFnxJTgQwfVuYEdQRWVAywE";
    private GroupRepositoryInterface $groupRepository;
    private Logger $logger;
    private CustomerRepositoryInterface $customerRepository;
    private CountryInformationAcquirerInterface $countryRepository;
    private TimezoneInterface $time;
    private CustomerMetadataInterface $customerMetadata;
    private Session $authSession;

    public function __construct(
        Context $context,
        GroupRepositoryInterface $groupRepository,
        CustomerRepositoryInterface $customerRepository,
        CountryInformationAcquirerInterface $countryRepository,
        TimezoneInterface $time,
        CustomerMetadataInterface $customerMetadata,
        Session $authSession,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->_request = $context->getRequest();
        $this->groupRepository = $groupRepository;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->countryRepository = $countryRepository;
        $this->time = $time;
        $this->customerMetadata = $customerMetadata;
        $this->authSession = $authSession;
    }

    /**
     * @return string
     */
    public function getBaseURL(): string
    {
        $url = $this->scopeConfig->getValue(self::XML_PATH_BASE_URL);
        if (empty($url)) {
            return $this->baseURL;
        }
        return rtrim($url, ' /');
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        $clientID = $this->scopeConfig->getValue(self::XML_PATH_CLIENT_ID);
        if (empty($clientID)) {
            return $this->clientID;
        }
        return $clientID;
    }

    /**
     * @param CustomerInterface $customer
     * @return array
     */
    public function getCustomerFields(CustomerInterface $customer): array
    {
        $data = [
            'prefix' => $customer->getPrefix(),
            'first_name' => $customer->getFirstname(),
            'middle_name' => $customer->getMiddlename(),
            'last_name' => $customer->getLastname(),
            'suffix' => $customer->getSuffix(),
            'email' => $customer->getEmail(),
            'created_at' => $this->formatDate($customer->getCreatedAt()),
            'updated_at' => $this->now(),
            'created_in' => $customer->getCreatedIn(),
            'dob' => $this->formatDate($customer->getDob()),
        ];

        try {
            $genderAttribute = $this->customerMetadata->getAttributeMetadata('gender');
            $data['gender'] = $genderAttribute->getOptions()[$customer->getGender()]->getLabel();
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->logger->error($e, 'Failed to fetch customer gender details');
        }

        $groupId = $customer->getGroupId();
        if (!empty($groupId)) {
            try {
                $group = $this->groupRepository->getById($groupId);
                if (!empty($group)) {
                    $data['group'] = $group->getCode();
                }
            } catch (NoSuchEntityException|LocalizedException $e) {
                $this->logger->error($e, 'Failed to fetch customer group details');
            }
        }

        try {
            $customer = $this->customerRepository->getById($customer->getId());
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->logger->error($e, 'Failed to fetch customer details');
            return $data;
        }

        $addresses = $customer->getAddresses();
        if (!empty($addresses)) {
            foreach ($addresses as $address) {
                if ($address->isDefaultBilling()) {
                    $data['billing_address'] = $this->getAddressFields($address);
                    continue;
                }
                if ($address->isDefaultShipping()) {
                    $data['shipping_address'] = $this->getAddressFields($address);
                }
            }
        }

        $attributes = $customer->getCustomAttributes();
        $customAttrs = [];
        if (!empty($attributes)) {
            foreach ($attributes as $attr) {
                $customAttrs[$attr->getAttributeCode()] = $attr->getValue();
            }
            $data['custom_attributes'] = $customAttrs;
        }

        return $data;
    }

    private function getAddressFields(AddressInterface $address): array
    {
        $data = [
            'city' => $address->getCity(),
            'street_lines' => $address->getStreet(),
            'post_code' => $address->getPostcode(),
            'prefix' => $address->getPrefix(),
            'first_name' => $address->getFirstname(),
            'middle_name' => $address->getMiddlename(),
            'last_name' => $address->getLastname(),
            'suffix' => $address->getSuffix(),
            'company' => $address->getCompany(),
            'vat' => $address->getVatId(),
        ];

        $region = $address->getRegion();
        if ($region instanceof RegionInterface) {
            $data['region'] = [
                'code' => $region->getRegionCode(),
                'name' => $region->getRegion(),
            ];
        }

        try {
            $country = $this->countryRepository->getCountryInfo($address->getCountryId());
            if (!empty($country)) {
                $data['country'] = [
                    'id' => $country->getId(),
                    'name_en' => $country->getFullNameEnglish(),
                    'name_local' => $country->getFullNameLocale(),
                    'abbr2' => $country->getTwoLetterAbbreviation(),
                    'abbr3' => $country->getThreeLetterAbbreviation(),
                ];
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e, 'Failed to fetch country details');
        }

        return $data;
    }

    /**
     * @return array|null
     */
    public function getAdminUserFields(): ?array
    {
        $user = $this->authSession->getUser();
        if (!empty($user)) {
            return [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'username' => $user->getUserName(),
            ];
        }
        $this->logger->warn("Failed to retrieve admin user details");
        return null;
    }

    public function formatDate(string $value): string
    {
        if (empty($value)) {
            return "";
        }
        $date = date_create($value);
        if ($date) {
            return $this->time->date($date)->format(self::DATE_TIME_FORMAT);
        }

        $this->logger->warn("Invalid time value", ["value" => $value]);
        return $value;
    }

    public function now(): string
    {
        return $this->time->date()->format(self::DATE_TIME_FORMAT);
    }
}
