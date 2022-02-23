<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Helper;

use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
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
use Magento\Newsletter\Model\Subscriber;

class Data extends AbstractHelper
{
    private string $baseURL = "https://magento-integration-api.autopilotapp.com";
    private string $clientID = "mgqQkvCJWDFnxJTgQwfVuYEdQRWVAywE";
    private GroupRepositoryInterface $groupRepository;
    private AutopilotLoggerInterface $logger;
    private CountryInformationAcquirerInterface $countryRepository;
    private TimezoneInterface $time;
    private CustomerMetadataInterface $customerMetadata;
    private Subscriber $subscriber;

    public function __construct(
        Context $context,
        GroupRepositoryInterface $groupRepository,
        CountryInformationAcquirerInterface $countryRepository,
        TimezoneInterface $time,
        CustomerMetadataInterface $customerMetadata,
        Subscriber $subscriber,
        AutopilotLoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->_request = $context->getRequest();
        $this->groupRepository = $groupRepository;
        $this->logger = $logger;
        $this->countryRepository = $countryRepository;
        $this->time = $time;
        $this->customerMetadata = $customerMetadata;
        $this->subscriber = $subscriber;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getAutopilotURL(string $path): string
    {
        $path = trim($path);
        $url = $this->scopeConfig->getValue(Config::XML_PATH_BASE_URL);
        if (empty($url)) {
            $url = $this->baseURL;
        }
        if (empty($path)) {
            return rtrim($url, ' /');
        }
        return rtrim($url, ' /') . '/' . ltrim($path, '/');
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        $clientID = $this->scopeConfig->getValue(Config::XML_PATH_CLIENT_ID);
        if (empty($clientID)) {
            return $this->clientID;
        }
        return $clientID;
    }

    /**
     * @param CustomerInterface $customer
     * @param ConfigScopeInterface $scope
     * @return array
     */
    public function getCustomerFields(CustomerInterface $customer, ConfigScopeInterface $scope): array
    {
        $sub = $this->subscriber->loadByCustomer($customer->getId(), $customer->getWebsiteId());
        $isSubscribed = $sub->isSubscribed();
        if (!$scope->isNonSubscribedCustomerSyncEnabled() && !$isSubscribed) {
            return [];
        }
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
            'is_subscribed' => $isSubscribed,
        ];

        try {
            $gender = $customer->getGender();
            if (!empty($gender)) {
                $genderAttribute = $this->customerMetadata->getAttributeMetadata('gender');
                $data['gender'] = $genderAttribute->getOptions()[$gender]->getLabel();
            }
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

    public function formatDate(?string $value): string
    {
        if (empty($value)) {
            return Config::EMPTY_DATE_TIME;
        }
        $date = date_create($value);
        if ($date) {
            return $this->time->date($date)->format(Config::DATE_TIME_FORMAT);
        }

        $this->logger->warn("Invalid time value", ["value" => $value]);
        return Config::EMPTY_DATE_TIME;
    }

    public function getErrorResponse(string $message): array
    {
        return [
            'error' => true,
            'message' => $message,
        ];
    }

    public function now(): string
    {
        return $this->time->date()->format(Config::DATE_TIME_FORMAT);
    }
}
