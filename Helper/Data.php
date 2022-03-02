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
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

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
        $sub = $this->subscriber->loadByCustomer((int)$customer->getId(), (int)$customer->getWebsiteId());
        $isSubscribed = $sub->isSubscribed();
        if (!$scope->isNonSubscribedCustomerSyncEnabled() && !$isSubscribed) {
            return [];
        }
        $data = [
            'id' => (int)$customer->getId(),
            'prefix' => $customer->getPrefix(),
            'first_name' => $customer->getFirstname(),
            'middle_name' => $customer->getMiddlename(),
            'last_name' => $customer->getLastname(),
            'suffix' => $customer->getSuffix(),
            'email' => $customer->getEmail(),
            'created_at' => $this->formatDate($customer->getCreatedAt()),
            'updated_at' => $this->formatDate($customer->getUpdatedAt()),
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

    /**
     * @param OrderInterface[] $orders
     * @return array
     * @throws LocalizedException
     */
    private function getOrderFields(array $orders): array
    {
        $result = [];
        foreach ($orders as $order) {
            $orderData = [
                'id' => $order->getEntityId(),
                'status' => $order->getStatus(),
                'created_at' => $this->formatDate($order->getCreatedAt()),
                'updated_at' => $this->formatDate($order->getUpdatedAt()),
                'ip_address' => $order->getRemoteIp(),
                'total_canceled' => $order->getTotalCanceled(),
                'total_due' => $order->getTotalDue(),
                'total_invoiced' => $order->getTotalInvoiced(),
                'total_offline_refunded' => $order->getTotalOfflineRefunded(),
                'total_online_refunded' => $order->getTotalOnlineRefunded(),
                'grand_total' => $order->getGrandTotal(),
                'subtotal' => $order->getSubtotal(),
                'total_paid' => $order->getTotalPaid(),
                'base_total_canceled' => $order->getBaseTotalCanceled(),
                'base_total_due' => $order->getBaseTotalDue(),
                'base_total_invoiced' => $order->getBaseTotalInvoiced(),
                'base_total_offline_refunded' => $order->getBaseTotalOfflineRefunded(),
                'base_total_online_refunded' => $order->getBaseTotalOnlineRefunded(),
                'base_grand_total' => $order->getBaseGrandTotal(),
                'base_total_paid' => $order->getBaseTotalPaid(),
                'base_currency_code' => $order->getBaseCurrencyCode(),
                'global_currency_code' => $order->getGlobalCurrencyCode(),
                'order_currency_code' => $order->getOrderCurrencyCode(),
                'shipping_amount' => $order->getShippingAmount(),
                'shipping_tax_amount' => $order->getShippingTaxAmount(),
                'shipping_incl_tax' => $order->getShippingInclTax(),
                'shipping_invoiced' => $order->getShippingInvoiced(),
                'shipping_refunded' => $order->getShippingRefunded(),
                'shipping_canceled' => $order->getShippingCanceled(),
                'payment_authorization_amount' => $order->getPaymentAuthorizationAmount(),
                'payment' => $this->getPaymentFields($order->getPayment()),
                'shipping_description' => $order->getShippingDescription(),
                'items' => $this->getOrderItemFields($order->getItems()),
            ];

            $result[] = $orderData;
        }
        return $result;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return array
     */
    private function getPaymentFields(OrderPaymentInterface $payment): array
    {
        if (empty($payment)) {
            return [];
        }
        return [
            'method' => $payment->getMethod(),
            'additional_data' => $payment->getAdditionalData(),
            'account_status' => $payment->getAccountStatus(),
            'amount_paid' => $payment->getAmountPaid(),
            'amount_refunded' => $payment->getAmountRefunded(),
            'amount_ordered' => $payment->getAmountOrdered(),
            'address_status' => $payment->getAddressStatus(),
            'amount_authorized' => $payment->getAmountAuthorized(),
            'anet_trans_method' => $payment->getAnetTransMethod(),
            'cc_last_4' => $payment->getCcLast4(),
            'cc_owner' => $payment->getCcOwner(),
            'cc_transaction_id' => $payment->getCcTransId(),
            'cc_type' => $payment->getCcType(),
            'last_transaction_id' => $payment->getLastTransId(),
            'po_number' => $payment->getPoNumber(),
            'e_check_account_name' => $payment->getEcheckAccountName(),
            'e_check_account_type' => $payment->getEcheckAccountType(),
            'e_check_bank_name' => $payment->getEcheckBankName(),
            'e_check_type' => $payment->getEcheckType(),
        ];
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
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'created_at' => $this->formatDate($item->getCreatedAt()),
                'updated_at' => $this->formatDate($item->getUpdatedAt()),
                'amount_refunded' => $item->getAmountRefunded(),
                'base_amount_refunded' => $item->getBaseAmountRefunded(),
                'base_cost' => $item->getBaseCost(),
                'additional_data' => $item->getAdditionalData(),
                'base_discount' => $item->getBaseDiscountAmount(),
                'discount_percent' => $item->getDiscountPercent(),
                'discount_invoiced' => $item->getDiscountInvoiced(),
                'discount_refunded' => $item->getDiscountRefunded(),
                'base_discount_invoiced' => $item->getBaseDiscountInvoiced(),
                'base_discount_amount' => $item->getBaseDiscountAmount(),
                'base_discount_refunded' => $item->getBaseDiscountRefunded(),
                'price' => $item->getPrice(),
                'base_price' => $item->getBasePrice(),
                'original_price' => $item->getOriginalPrice(),
                'base_original_price' => $item->getBaseOriginalPrice(),
                'product_type' => $item->getProductType(),
                'qty_ordered' => $item->getQtyOrdered(),
                'qty_back_ordered' => $item->getQtyBackordered(),
                'qty_refunded' => $item->getQtyRefunded(),
                'qty_returned' => $item->getQtyReturned(),
                'qty_canceled' => $item->getQtyCanceled(),
                'qty_shipped' => $item->getQtyShipped(),
                'gty_invoiced' => $item->getQtyInvoiced(),
                'total' => $item->getRowTotal(),
                'sku' => $item->getSku(),
                'tax_amount' => $item->getTaxAmount(),
                'tax_percent' => $item->getTaxPercent(),
            ];
        }
        return $result;
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


    /**
     * @return \DateTime
     */
    public function now(): \DateTime
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
