<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Helper;

use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\SyncCategoryInterface;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\Api\ProductDataFactory;
use Autopilot\AP3Connector\Model\ResourceModel\CronCheckpoint\Collection as CheckpointCollection;
use Autopilot\AP3Connector\Model\ResourceModel\SyncJob\Collection as JobCollection;
use AutoPilot\AP3Connector\Model\ResourceModel\CronCheckpoint\CollectionFactory as CheckpointCollectionFactory;
use AutoPilot\AP3Connector\Model\ResourceModel\SyncJob\CollectionFactory as JobCollectionFactory;
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
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Autopilot\AP3Connector\Api\ConfigurationReaderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use DateTime;
use Exception;

class Data extends AbstractHelper
{
    private const SHIPPING_ADDRESS = "shipping_address";
    private const BILLING_ADDRESS = "billing_address";
    private const ORDERS = "orders";

    private string $baseURL = "https://magento-integration-api.autopilotapp.com";
    private string $clientID = "mgqQkvCJWDFnxJTgQwfVuYEdQRWVAywE";
    private GroupRepositoryInterface $groupRepository;
    private AutopilotLoggerInterface $logger;
    private CountryInformationAcquirerInterface $countryRepository;
    private TimezoneInterface $time;
    private CustomerMetadataInterface $customerMetadata;
    private Subscriber $subscriber;
    private ConfigurationReaderInterface $config;
    private CheckpointCollectionFactory $checkpointCollectionFactory;
    private JobCollectionFactory $jobCollectionFactory;
    private ProductDataFactory $productDataFactory;

    public function __construct(
        Context $context,
        GroupRepositoryInterface $groupRepository,
        CountryInformationAcquirerInterface $countryRepository,
        TimezoneInterface $time,
        CustomerMetadataInterface $customerMetadata,
        Subscriber $subscriber,
        AutopilotLoggerInterface $logger,
        ConfigurationReaderInterface $config,
        CheckpointCollectionFactory $checkpointCollectionFactory,
        JobCollectionFactory $jobCollectionFactory,
        ProductDataFactory $productDataFactory
    ) {
        parent::__construct($context);
        $this->_request = $context->getRequest();
        $this->groupRepository = $groupRepository;
        $this->logger = $logger;
        $this->countryRepository = $countryRepository;
        $this->time = $time;
        $this->customerMetadata = $customerMetadata;
        $this->subscriber = $subscriber;
        $this->config = $config;
        $this->checkpointCollectionFactory = $checkpointCollectionFactory;
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->productDataFactory = $productDataFactory;
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
            'created_at' => $this->formatDate($customer->getCreatedAt()),
            'updated_at' => $this->formatDate($customer->getUpdatedAt()),
            'created_in' => (string)$customer->getCreatedIn(),
            'dob' => $this->formatDate($customer->getDob()),
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
                    $data[self::BILLING_ADDRESS] = $this->getAddressFields($address);
                }
                if ($address->isDefaultShipping()) {
                    $data[self::SHIPPING_ADDRESS] = $this->getAddressFields($address);
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
            $orderFields = $this->getOrderFields($order);
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
                    'dob' => $this->formatDate($order->getCustomerDob()),
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
     * @param OrderInterface|Order $order
     * @return array
     */
    private function getOrderFields(OrderInterface $order): array
    {
        $fields = [
            'id' => To::int($order->getEntityId()),
            'is_virtual' => To::bool($order->getIsVirtual()),
            'number' => (string)$order->getIncrementId(),
            'status' => (string)$order->getStatus(),
            'state' => (string)$order->getState(),
            'quantity' => To::float($order->getTotalQtyOrdered()),
            'base_quantity' => To::float($order->getBaseTotalQtyOrdered()),
            'created_at' => $this->formatDate($order->getCreatedAt()),
            'updated_at' => $this->formatDate($order->getUpdatedAt()),
            'ip_address' => $order->getRemoteIp(),
            'total_due' => To::float($order->getTotalDue()),
            'base_total_due' => To::float($order->getBaseTotalDue()),
            'total_invoiced' => To::float($order->getTotalInvoiced()),
            'base_total_invoiced' => To::float($order->getBaseTotalInvoiced()),
            'total_offline_refunded' => To::float($order->getTotalOfflineRefunded()),
            'base_total_offline_refunded' => To::float($order->getBaseTotalOfflineRefunded()),
            'total_online_refunded' => To::float($order->getTotalOnlineRefunded()),
            'base_total_online_refunded' => To::float($order->getBaseTotalOnlineRefunded()),
            'grand_total' => To::float($order->getGrandTotal()),
            'base_grand_total' => To::float($order->getBaseGrandTotal()),
            'subtotal' => To::float($order->getSubtotal()),
            'base_subtotal' => To::float($order->getBaseSubtotal()),
            'subtotal_incl_tax' => To::float($order->getSubtotalInclTax()),
            'base_subtotal_incl_tax' => To::float($order->getBaseSubtotalInclTax()),
            'total_paid' => To::float($order->getTotalPaid()),
            'base_total_paid' => To::float($order->getBaseTotalPaid()),
            'total_cancelled' => To::float($order->getTotalCanceled()),
            'base_total_cancelled' => To::float($order->getBaseTotalCanceled()),
            'base_currency_code' => (string)$order->getBaseCurrencyCode(),
            'global_currency_code' => (string)$order->getGlobalCurrencyCode(),
            'order_currency_code' => (string)$order->getOrderCurrencyCode(),
            'shipping' => To::float($order->getShippingAmount()),
            'base_shipping' => To::float($order->getBaseShippingAmount()),
            'shipping_tax' => To::float($order->getShippingTaxAmount()),
            'base_shipping_tax' => To::float($order->getBaseShippingTaxAmount()),
            'shipping_incl_tax' => To::float($order->getShippingInclTax()),
            'base_shipping_incl_tax' => To::float($order->getBaseShippingInclTax()),
            'shipping_invoiced' => To::float($order->getShippingInvoiced()),
            'base_shipping_invoiced' => To::float($order->getBaseShippingInvoiced()),
            'shipping_refunded' => To::float($order->getShippingRefunded()),
            'base_shipping_refunded' => To::float($order->getBaseShippingRefunded()),
            'shipping_canceled' => To::float($order->getShippingCanceled()),
            'base_shipping_canceled' => To::float($order->getBaseShippingCanceled()),
            'tax' => To::float($order->getTaxAmount()),
            'base_tax' => To::float($order->getBaseTaxAmount()),
            'tax_cancelled' => To::float($order->getTaxCanceled()),
            'base_tax_cancelled' => To::float($order->getBaseTaxCanceled()),
            'tax_invoiced' => To::float($order->getTaxInvoiced()),
            'base_tax_invoiced' => To::float($order->getBaseTaxInvoiced()),
            'tax_refunded' => To::float($order->getTaxRefunded()),
            'base_tax_refunded' => To::float($order->getBaseTaxRefunded()),
            'discount' => To::float($order->getDiscountAmount()),
            'base_discount' => To::float($order->getBaseDiscountAmount()),
            'discount_refunded' => To::float($order->getDiscountRefunded()),
            'base_discount_refunded' => To::float($order->getBaseDiscountRefunded()),
            'discount_cancelled' => To::float($order->getDiscountCanceled()),
            'base_discount_cancelled' => To::float($order->getBaseDiscountCanceled()),
            'discount_invoiced' => To::float($order->getDiscountInvoiced()),
            'base_discount_invoiced' => To::float($order->getBaseDiscountInvoiced()),
            'base_discount_description' => (string)$order->getDiscountDescription(),
            'shipping_discount' => To::float($order->getShippingDiscountAmount()),
            'base_shipping_discount' => To::float($order->getBaseShippingDiscountAmount()),
            'coupon_code' => (string)$order->getCouponCode(),
            'protect_code' => (string)$order->getProtectCode(),
            'canceled_at' => $this->getOrderCancellationDate($order),
            'items' => $this->getOrderItemFields($order->getAllVisibleItems()),
        ];

        $payment = $order->getPayment();
        if ($payment !== null) {
            $fields['payment_method'] = (string)$payment->getMethod();
            $fields['last_transaction_id'] = (string)$payment->getLastTransId();
        }

        $extensionAttrs = $order->getExtensionAttributes();
        if ($extensionAttrs instanceof OrderExtensionInterface) {
            $ext = [];
            $amz = $extensionAttrs->getAmazonOrderReferenceId();
            if (!empty($amz)) {
                $ext['amazon_reference_id'] = To::int($amz->getOrderId());
            }

            $gift = $extensionAttrs->getGiftMessage();

            if (!empty($gift)) {
                $ext['gift'] = [
                    'message' => (string)$gift->getMessage(),
                    'sender' => (string)$gift->getSender(),
                    'recipient' => (string)$gift->getRecipient(),
                ];
            }

            if (!empty($ext)) {
                $fields['extension'] = $ext;
            }
        }

        if ($order instanceof Order) {
            $addresses = $order->getAddresses();
            foreach ($addresses as $address) {
                switch ($address->getAddressType()) {
                    case "shipping":
                        if (!$order->getIsVirtual()) {
                            $shippingAddress = $this->getAddressFields($address);
                            $fields[self::SHIPPING_ADDRESS] = $shippingAddress;
                        }
                        break;
                    case "billing":
                        $billingAddress = $this->getAddressFields($address);
                        $fields[self::BILLING_ADDRESS] = $billingAddress;
                        break;
                }
            }
        }

        return $fields;
    }

    private function getOrderCancellationDate(OrderInterface $order): string
    {
        $status = (string)$order->getStatus();
        if ($status !== Order::STATE_CANCELED) {
            return Config::EMPTY_DATE_TIME;
        }
        $attr = $order->getExtensionAttributes();
        if (!empty($attr)) {
            $canceledAt = $attr->getAutopilotCanceledAt();
            if (!empty($canceledAt)) {
                return $this->formatDate($canceledAt);
            }
        }
        return $this->formatDate($order->getCreatedAt());
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
     * @param OrderItemInterface[] $items
     * @return array
     */
    private function getOrderItemFields(array $items): array
    {
        if (empty($items)) {
            return [];
        }
        $result = [];
        foreach ($items as $item) {
            $sku = (string)$item->getSku();
            $product = $this->productDataFactory->create();
            if (!$product->loadById(To::int($item->getProductId()))) {
                continue;
            }
            $result[] = [
                'id' => To::int($item->getItemId()),
                'is_virtual' => To::bool($item->getIsVirtual()),
                'name' => (string)$item->getName(),
                'sku' => $sku,
                'description' => (string)$item->getDescription(),
                'created_at' => $this->formatDate($item->getCreatedAt()),
                'updated_at' => $this->formatDate($item->getUpdatedAt()),
                'refunded' => To::float($item->getAmountRefunded()),
                'base_refunded' => To::float($item->getBaseAmountRefunded()),
                'base_cost' => To::float($item->getBaseCost()),
                'discount' => To::float($item->getDiscountAmount()),
                'base_discount' => To::float($item->getBaseDiscountAmount()),
                'discount_percent' => To::float($item->getDiscountPercent()),
                'discount_invoiced' => To::float($item->getDiscountInvoiced()),
                'base_discount_invoiced' => To::float($item->getBaseDiscountInvoiced()),
                'discount_refunded' => To::float($item->getDiscountRefunded()),
                'base_discount_refunded' => To::float($item->getBaseDiscountRefunded()),
                'total' => To::float($item->getRowTotal()),
                'base_total' => To::float($item->getBaseRowTotal()),
                'total_incl_tax' => To::float($item->getRowTotalInclTax()),
                'base_total_incl_tax' => To::float($item->getBaseRowTotalInclTax()),
                'price' => To::float($item->getPrice()),
                'base_price' => To::float($item->getBasePrice()),
                'original_price' => To::float($item->getOriginalPrice()),
                'base_original_price' => To::float($item->getBaseOriginalPrice()),
                'qty_ordered' => To::float($item->getQtyOrdered()),
                'qty_back_ordered' => To::float($item->getQtyBackordered()),
                'qty_refunded' => To::float($item->getQtyRefunded()),
                'qty_returned' => To::float($item->getQtyReturned()),
                'qty_cancelled' => To::float($item->getQtyCanceled()),
                'qty_shipped' => To::float($item->getQtyShipped()),
                'gty_invoiced' => To::float($item->getQtyInvoiced()),
                'tax' => To::float($item->getTaxAmount()),
                'base_tax' => To::float($item->getBaseTaxAmount()),
                'is_free_shipping' => $item->getFreeShipping(),
                'tax_percent' => To::float($item->getTaxPercent()),
                'additional_data' => (string)$item->getAdditionalData(),
                'store_id' => To::int($item->getStoreId()),
                'product' => $product->toArray(),
                'variation' => $product->getVariationDataBySKU($sku),
            ];
        }
        return $result;
    }

    /**
     * @param AddressInterface|OrderAddressInterface $address
     * @return array
     */
    private function getAddressFields($address): array
    {
        $data = [
            'city' => (string)$address->getCity(),
            'street_lines' => $address->getStreet(),
            'post_code' => (string)$address->getPostcode(),
            'prefix' => (string)$address->getPrefix(),
            'first_name' => (string)$address->getFirstname(),
            'middle_name' => (string)$address->getMiddlename(),
            'last_name' => (string)$address->getLastname(),
            'suffix' => (string)$address->getSuffix(),
            'company' => (string)$address->getCompany(),
            'vat' => (string)$address->getVatId(),
            'phone' => (string)$address->getTelephone(),
            'fax' => (string)$address->getFax(),
        ];

        $region = $address->getRegion();
        if ($region instanceof RegionInterface) {
            $data['region'] = [
                'code' => (string)$region->getRegionCode(),
                'name' => (string)$region->getRegion(),
            ];
        }

        try {
            $country = $this->countryRepository->getCountryInfo($address->getCountryId());
            if (!empty($country)) {
                $data['country'] = [
                    'name_en' => (string)$country->getFullNameEnglish(),
                    'name_local' => (string)$country->getFullNameLocale(),
                    'abbr2' => (string)$country->getTwoLetterAbbreviation(),
                    'abbr3' => (string)$country->getThreeLetterAbbreviation(),
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

    /**
     * @param DateTime|null $value
     * @return string
     */
    public function formatDateTime($value): string
    {
        if (empty($value)) {
            return Config::EMPTY_DATE_TIME;
        }

        return $value->format(Config::DATE_TIME_FORMAT);
    }

    /**
     * @return DateTime
     */
    public function nowInClientTimezone(): DateTime
    {
        return $this->time->date();
    }

    /**
     * @return DateTime
     */
    public function now(): DateTime
    {
        return date_create();
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
