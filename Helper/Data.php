<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Helper;

use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\SyncCategoryInterface;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\Api\OrderDataFactory;
use Autopilot\AP3Connector\Model\Api\AddressDataFactory;
use Autopilot\AP3Connector\Model\ResourceModel\CronCheckpoint\Collection as CheckpointCollection;
use Autopilot\AP3Connector\Model\ResourceModel\SyncJob\Collection as JobCollection;
use Autopilot\AP3Connector\Model\ResourceModel\CronCheckpoint\CollectionFactory as CheckpointCollectionFactory;
use Autopilot\AP3Connector\Model\ResourceModel\SyncJob\CollectionFactory as JobCollectionFactory;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Api\Data\OrderInterface;
use Autopilot\AP3Connector\Api\ConfigurationReaderInterface;
use Magento\Store\Model\ScopeInterface;
use DateTime;
use Exception;

class Data extends AbstractHelper
{
    public const SHIPPING_ADDRESS = "shipping_address";
    public const BILLING_ADDRESS = "billing_address";

    private const ORDERS = "orders";

    private string $baseURL = "https://magento-integration-api.autopilotapp.com";
    private string $clientID = "mgqQkvCJWDFnxJTgQwfVuYEdQRWVAywE";
    private GroupRepositoryInterface $groupRepository;
    private AutopilotLoggerInterface $logger;
    private TimezoneInterface $timezone;
    private CustomerMetadataInterface $customerMetadata;
    private Subscriber $subscriber;
    private ConfigurationReaderInterface $config;
    private CheckpointCollectionFactory $checkpointCollectionFactory;
    private JobCollectionFactory $jobCollectionFactory;
    private OrderDataFactory $orderDataFactory;
    private AddressDataFactory $addressDataFactory;
    private \DateTimeZone $utcTZ;

    public function __construct(
        Context $context,
        GroupRepositoryInterface $groupRepository,
        TimezoneInterface $timezone,
        CustomerMetadataInterface $customerMetadata,
        Subscriber $subscriber,
        AutopilotLoggerInterface $logger,
        ConfigurationReaderInterface $config,
        CheckpointCollectionFactory $checkpointCollectionFactory,
        JobCollectionFactory $jobCollectionFactory,
        OrderDataFactory $orderDataFactory,
        AddressDataFactory $addressDataFactory
    ) {
        parent::__construct($context);
        $this->_request = $context->getRequest();
        $this->groupRepository = $groupRepository;
        $this->logger = $logger;
        $this->timezone = $timezone;
        $this->customerMetadata = $customerMetadata;
        $this->subscriber = $subscriber;
        $this->config = $config;
        $this->checkpointCollectionFactory = $checkpointCollectionFactory;
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->orderDataFactory = $orderDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->utcTZ = timezone_open('UTC');
    }

    /**
     * @param string $path
     * @return string
     */
    public function getAutopilotURL(string $path): string
    {
        $path = trim($path);
        $url = (string)$this->scopeConfig->getValue(Config::XML_PATH_BASE_URL);
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
        $sub = $this->subscriber->loadByCustomer(To::int($customer->getId()), To::int($customer->getWebsiteId()));
        $isSubscribed = $sub->isSubscribed();
        if (!$this->config->isNonSubscribedCustomerSyncEnabled($scope->getType(), $scope->getId()) && !$isSubscribed) {
            return [];
        }
        $data = [
            'id' => To::int($customer->getId()),
            'prefix' => (string)$customer->getPrefix(),
            'first_name' => (string)$customer->getFirstname(),
            'middle_name' => (string)$customer->getMiddlename(),
            'last_name' => (string)$customer->getLastname(),
            'suffix' => (string)$customer->getSuffix(),
            'email' => (string)$customer->getEmail(),
            'created_at' => $this->toUTC($customer->getCreatedAt()),
            'updated_at' => $this->toUTC($customer->getUpdatedAt()),
            'created_in' => (string)$customer->getCreatedIn(),
            'dob' => $this->toUTC($customer->getDob()),
            'gender' => $this->getGenderLabel($customer->getGender()),
            'is_subscribed' => $isSubscribed,
        ];

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
                    $data[self::BILLING_ADDRESS] = $this->addressDataFactory->create()->toArray($address);
                }
                if ($address->isDefaultShipping()) {
                    $data[self::SHIPPING_ADDRESS] = $this->addressDataFactory->create()->toArray($address);
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

    /**
     * @param OrderInterface[] $orders
     * @param ConfigScopeInterface $scope
     * @return array
     */
    public function getCustomerWithOrderFields(array $orders, ConfigScopeInterface $scope): array
    {
        $isAnonymousOrderEnabled = $this->config->isAnonymousOrderSyncEnabled($scope->getType(), $scope->getId());
        $nonSubscribedEnabled = $this->config->isNonSubscribedCustomerSyncEnabled($scope->getType(), $scope->getId());
        $orderGroups = [];
        foreach ($orders as $order) {
            $customerId = To::int($order->getCustomerId());
            $customerEmail = To::email($order->getCustomerEmail());
            if (($customerId == 0 && !$isAnonymousOrderEnabled) || empty($customerEmail)) {
                continue;
            }
            $key = sprintf("%d:%s", $customerId, $customerEmail);
            $orderFields = $this->orderDataFactory->create()->toArray($order);
            if (array_has($orderGroups, $key)) {
                $orderGroups[$key][self::ORDERS][] = $orderFields;
            } else {
                $sub = $this->subscriber->loadBySubscriberEmail($customerEmail, $scope->getWebsiteId());
                $isSubscribed = $sub->isSubscribed();
                if (!$isSubscribed && !$nonSubscribedEnabled) {
                    continue;
                }
                $customer = [
                    'id' => $customerId,
                    'prefix' => (string)$order->getCustomerPrefix(),
                    'first_name' => (string)$order->getCustomerFirstname(),
                    'middle_name' => (string)$order->getCustomerMiddlename(),
                    'last_name' => (string)$order->getCustomerLastname(),
                    'suffix' => (string)$order->getCustomerSuffix(),
                    'email' => $customerEmail,
                    'dob' => $this->toUTC($order->getCustomerDob()),
                    'gender' => $this->getGenderLabel($order->getCustomerGender()),
                    'is_subscribed' => $isSubscribed,
                    self::ORDERS => [$orderFields],
                ];
                if ($customerId === 0) {
                    if (array_has($orderFields, self::SHIPPING_ADDRESS)) {
                        $customer[self::SHIPPING_ADDRESS] = $orderFields[self::SHIPPING_ADDRESS];
                    }
                    if (array_has($orderFields, self::BILLING_ADDRESS)) {
                        $customer[self::BILLING_ADDRESS] = $orderFields[self::BILLING_ADDRESS];
                    }
                }
                $orderGroups[$key] = $customer;
            }
        }
        $result = [];
        foreach ($orderGroups as $customer) {
            $result[] = $customer;
        }
        return $result;
    }

    /**
     * @param mixed|null|string $gender
     * @return string
     */
    private function getGenderLabel($gender): string
    {
        if (empty($gender)) {
            return "";
        }
        try {
            $genderAttribute = $this->customerMetadata->getAttributeMetadata('gender');
            return (string)$genderAttribute->getOptions()[$gender]->getLabel();
        } catch (Exception $e) {
            $this->logger->error($e, 'Failed to fetch customer gender details');
            return "";
        }
    }


    /**
     * @param DateTime|string|null $value
     * @return string
     */
    public function toUTC($value): string
    {
        switch (true) {
            case is_string($value):
                $date = date_create($value, $this->utcTZ);
                if ($date) {
                    return $date->format(Config::DATE_TIME_FORMAT);
                }
                $this->logger->warn("Invalid date time", ['value' => $value]);
                return Config::EMPTY_DATE_TIME;
            case $value instanceof DateTime:
                $value->setTimezone($this->utcTZ);
                return $value->format(Config::DATE_TIME_FORMAT);
            default:
                return Config::EMPTY_DATE_TIME;
        }
    }

    /**
     * @return DateTime
     */
    public function nowInClientTimezone(): DateTime
    {
        return $this->timezone->date();
    }

    /**
     * @return DateTime
     */
    public function nowUTC(): DateTime
    {
        return date_create('now', $this->utcTZ);
    }

    public function getErrorResponse(string $message): array
    {
        return [
            'error' => true,
            'message' => $message,
        ];
    }

    /**
     * @return CheckpointCollection
     * @throws Exception
     */
    public function createCheckpointCollection()
    {
        $collection = $this->checkpointCollectionFactory->create();
        if ($collection instanceof CheckpointCollection) {
            return $collection;
        }
        throw new Exception("Invalid checkpoint collection type");
    }

    /**
     * @return JobCollection
     * @throws Exception
     */
    public function createJobCollection()
    {
        $collection = $this->jobCollectionFactory->create();
        if ($collection instanceof JobCollection) {
            return $collection;
        }
        throw new Exception("Invalid job collection type");
    }

    public function shouldExportCustomer(ConfigScopeInterface $scope, CustomerInterface $customer): bool
    {
        if (!$this->config->isAutoSyncEnabled($scope->getType(), $scope->getId(), SyncCategoryInterface::CUSTOMER)) {
            $this->logger->debug(
                sprintf("Automatic %s synchronisation is off", SyncCategoryInterface::CUSTOMER),
                $scope->toArray()
            );
            return false;
        }
        if ($scope->getType() == ScopeInterface::SCOPE_WEBSITE) {
            return $customer->getWebsiteId() == $scope->getId();
        }
        return $customer->getStoreId() == $scope->getId() && $customer->getWebsiteId() == $scope->getWebsiteId();
    }

    public function shouldExportOrder(ConfigScopeInterface $scope, OrderInterface $order): bool
    {
        if (!$this->config->isAutoSyncEnabled($scope->getType(), $scope->getId(), SyncCategoryInterface::ORDER)) {
            $this->logger->debug(
                sprintf("Automatic %s synchronisation is off", SyncCategoryInterface::ORDER),
                $scope->toArray()
            );
            return false;
        }
        return array_contains($scope->getStoreIds(), To::int($order->getStoreId()));
    }
}
