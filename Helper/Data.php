<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Helper;

use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\Data\CustomerOrderInterface;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use DateTime;
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
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Autopilot\AP3Connector\Api\ConfigurationReaderInterface;
use Magento\Sales\Model\Order;

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
    private ConfigurationReaderInterface $config;

    public function __construct(
        Context $context,
        GroupRepositoryInterface $groupRepository,
        CountryInformationAcquirerInterface $countryRepository,
        TimezoneInterface $time,
        CustomerMetadataInterface $customerMetadata,
        Subscriber $subscriber,
        AutopilotLoggerInterface $logger,
        ConfigurationReaderInterface $config
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
        $sub = $this->subscriber->loadByCustomer((int)$customer->getId(), (int)$customer->getWebsiteId());
        $isSubscribed = $sub->isSubscribed();
        if (!$this->config->isNonSubscribedCustomerSyncEnabled($scope->getType(), $scope->getId()) && !$isSubscribed) {
            return [];
        }
        $data = [
            'id' => (int)$customer->getId(),
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

    /**
     * @param CustomerOrderInterface $customer
     * @return array
     */
    public function getCustomerOrderFields(CustomerOrderInterface $customer): array
    {
        return [
            CustomerOrderInterface::CUSTOMER_ID => $customer->getCustomerId(),
            CustomerOrderInterface::CUSTOMER_EMAIL => $customer->getCustomerEmail(),
            CustomerOrderInterface::CUSTOMER_ORDERS => $this->getOrdersFields($customer->getOrders()),
        ];
    }

    /**
     * @param OrderInterface[] $orders
     * @return array
     */
    private function getOrdersFields(array $orders): array
    {
        $result = [];
        foreach ($orders as $order) {
            $orderData = [
                'id' => (int)$order->getEntityId(),
                'is_virtual' => (bool)$order->getIsVirtual(),
                'status' => (string)$order->getStatus(),
                'created_at' => $this->formatDate($order->getCreatedAt()),
                'updated_at' => $this->formatDate($order->getUpdatedAt()),
                'ip_address' => $order->getRemoteIp(),
                'total_canceled' => (float)$order->getTotalCanceled(),
                'total_due' => (float)$order->getTotalDue(),
                'total_invoiced' => (float)$order->getTotalInvoiced(),
                'total_offline_refunded' => (float)$order->getTotalOfflineRefunded(),
                'total_online_refunded' => (float)$order->getTotalOnlineRefunded(),
                'grand_total' => (float)$order->getGrandTotal(),
                'subtotal' => (float)$order->getSubtotal(),
                'total_paid' => (float)$order->getTotalPaid(),
                'base_total_canceled' => (float)$order->getBaseTotalCanceled(),
                'base_total_due' => (float)$order->getBaseTotalDue(),
                'base_total_invoiced' => (float)$order->getBaseTotalInvoiced(),
                'base_total_offline_refunded' => (float)$order->getBaseTotalOfflineRefunded(),
                'base_total_online_refunded' => (float)$order->getBaseTotalOnlineRefunded(),
                'base_grand_total' => (float)$order->getBaseGrandTotal(),
                'base_total_paid' => (float)$order->getBaseTotalPaid(),
                'base_currency_code' => (string)$order->getBaseCurrencyCode(),
                'global_currency_code' => (string)$order->getGlobalCurrencyCode(),
                'order_currency_code' => (string)$order->getOrderCurrencyCode(),
                'shipping_amount' => (float)$order->getShippingAmount(),
                'base_shipping_amount' => (float)$order->getBaseShippingAmount(),
                'shipping_tax_amount' => (float)$order->getShippingTaxAmount(),
                'base_shipping_tax_amount' => (float)$order->getBaseShippingTaxAmount(),
                'shipping_incl_tax' => (float)$order->getShippingInclTax(),
                'base_shipping_incl_tax' => (float)$order->getBaseShippingInclTax(),
                'shipping_invoiced' => (float)$order->getShippingInvoiced(),
                'base_shipping_invoiced' => (float)$order->getBaseShippingInvoiced(),
                'shipping_refunded' => (float)$order->getShippingRefunded(),
                'base_shipping_refunded' => (float)$order->getBaseShippingRefunded(),
                'shipping_canceled' => (float)$order->getShippingCanceled(),
                'base_shipping_canceled' => (float)$order->getBaseShippingCanceled(),
                'tax_amount' => (float)$order->getTaxAmount(),
                'base_tax_amount' => (float)$order->getBaseTaxAmount(),
                'tax_cancelled' => (float)$order->getTaxCanceled(),
                'base_tax_cancelled' => (float)$order->getBaseTaxCanceled(),
                'tax_invoiced' => (float)$order->getTaxInvoiced(),
                'base_tax_invoiced' => (float)$order->getBaseTaxInvoiced(),
                'tax_refunded' => (float)$order->getTaxRefunded(),
                'base_tax_refunded' => (float)$order->getBaseTaxRefunded(),
                'subtotal_incl_tax' => (float)$order->getSubtotalInclTax(),
                'discount_amount' => (float)$order->getDiscountAmount(),
                'base_discount_amount' => (float)$order->getBaseDiscountAmount(),
                'discount_refunded' => (float)$order->getDiscountRefunded(),
                'base_discount_refunded' => (float)$order->getBaseDiscountRefunded(),
                'discount_cancelled' => (float)$order->getDiscountCanceled(),
                'base_discount_cancelled' => (float)$order->getBaseDiscountCanceled(),
                'discount_invoiced' => (float)$order->getDiscountInvoiced(),
                'base_discount_invoiced' => (float)$order->getBaseDiscountInvoiced(),
                'base_discount_description' => (string)$order->getDiscountDescription(),
                'shipping_discount' => (float)$order->getShippingDiscountAmount(),
                'base_shipping_discount' => (float)$order->getBaseShippingDiscountAmount(),
                'coupon_code' => (string)$order->getCouponCode(),
                'protect_code' => (string)$order->getProtectCode(),
                'items' => $this->getOrderItemFields($order->getItems()),
            ];

            $payment = $order->getPayment();
            if ($payment !== null) {
                $orderData['payment_method'] = (string)$payment->getMethod();
                $orderData['last_transaction_id'] = (string)$payment->getLastTransId();
            }

            if ($order instanceof Order) {
                $addresses = $order->getAddresses();
                foreach ($addresses as $address) {
                    switch ($address->getAddressType()) {
                        case "shipping":
                            if (!$order->getIsVirtual()) {
                                $orderData["shipping_address"] = $this->getOrderAddressFields($address);
                            }
                            break;
                        case "billing":
                            $orderData["billing_address"] = $this->getOrderAddressFields($address);
                            break;
                    }

                }
            }
            $result[] = $orderData;
        }
        return $result;
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
            $result[] = [
                'name' => (string)$item->getName(),
                'description' => (string)$item->getDescription(),
                'created_at' => $this->formatDate($item->getCreatedAt()),
                'updated_at' => $this->formatDate($item->getUpdatedAt()),
                'amount_refunded' => (float)$item->getAmountRefunded(),
                'base_amount_refunded' => (float)$item->getBaseAmountRefunded(),
                'base_cost' => (float)$item->getBaseCost(),
                'additional_data' => (string)$item->getAdditionalData(),
                'base_discount' => (float)$item->getBaseDiscountAmount(),
                'discount_percent' => (float)$item->getDiscountPercent(),
                'discount_invoiced' => (float)$item->getDiscountInvoiced(),
                'discount_refunded' => (float)$item->getDiscountRefunded(),
                'base_discount_invoiced' => (float)$item->getBaseDiscountInvoiced(),
                'base_discount_amount' => (float)$item->getBaseDiscountAmount(),
                'base_discount_refunded' => (float)$item->getBaseDiscountRefunded(),
                'price' => (float)$item->getPrice(),
                'base_price' => (float)$item->getBasePrice(),
                'original_price' => (float)$item->getOriginalPrice(),
                'base_original_price' => (float)$item->getBaseOriginalPrice(),
                'product_type' => (string)$item->getProductType(),
                'qty_ordered' => (float)$item->getQtyOrdered(),
                'qty_back_ordered' => (float)$item->getQtyBackordered(),
                'qty_refunded' => (float)$item->getQtyRefunded(),
                'qty_returned' => (float)$item->getQtyReturned(),
                'qty_canceled' => (float)$item->getQtyCanceled(),
                'qty_shipped' => (float)$item->getQtyShipped(),
                'gty_invoiced' => (float)$item->getQtyInvoiced(),
                'total' => (float)$item->getRowTotal(),
                'sku' => (string)$item->getSku(),
                'tax_amount' => (float)$item->getTaxAmount(),
                'tax_percent' => (float)$item->getTaxPercent(),
            ];
        }
        return $result;
    }

    /**
     * @param OrderAddressInterface $address
     * @return array
     */
    private function getOrderAddressFields(OrderAddressInterface $address): array
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

    /**
     * @param AddressInterface $address
     * @return array
     */
    private function getAddressFields(AddressInterface $address): array
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
     * @return DateTime
     */
    public function now(): DateTime
    {
        return $this->time->date();
    }

    public function getErrorResponse(string $message): array
    {
        return [
            'error' => true,
            'message' => $message,
        ];
    }
}
