<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Helper;

use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use DateTime;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
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
use Magento\Catalog\Helper\Image;

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
    private ProductRepository $productRepository;
    private Image $imageHelper;

    public function __construct(
        Context $context,
        GroupRepositoryInterface $groupRepository,
        CountryInformationAcquirerInterface $countryRepository,
        TimezoneInterface $time,
        CustomerMetadataInterface $customerMetadata,
        Subscriber $subscriber,
        AutopilotLoggerInterface $logger,
        ConfigurationReaderInterface $config,
        ProductRepository $productRepository,
        Image $imageHelper
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
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
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
                    continue;
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
    public function getOrdersFields(array $orders, ConfigScopeInterface $scope): array
    {
        $isAnonymousOrderEnabled = $this->config->isAnonymousOrderSyncEnabled($scope->getType(), $scope->getId());
        $nonSubscribedEnabled = $this->config->isNonSubscribedCustomerSyncEnabled($scope->getType(), $scope->getId());
        $orderGroups = [];
        foreach ($orders as $order) {
            $customerId = (int)$order->getCustomerId();
            if ($customerId == 0 && !$isAnonymousOrderEnabled) {
                continue;
            }
            $customerEmail = (string)$order->getCustomerEmail();
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
     * @param OrderInterface $order
     * @return array
     */
    private function getOrderFields(OrderInterface $order): array
    {
        $fields = [
            'id' => (int)$order->getEntityId(),
            'is_virtual' => $order->getIsVirtual(),
            'number' => (string)$order->getIncrementId(),
            'status' => (string)$order->getStatus(),
            'created_at' => $this->formatDate($order->getCreatedAt()),
            'updated_at' => $this->formatDate($order->getUpdatedAt()),
            'ip_address' => $order->getRemoteIp(),
            'total_due' => (float)$order->getTotalDue(),
            'base_total_due' => (float)$order->getBaseTotalDue(),
            'total_invoiced' => (float)$order->getTotalInvoiced(),
            'base_total_invoiced' => (float)$order->getBaseTotalInvoiced(),
            'total_offline_refunded' => (float)$order->getTotalOfflineRefunded(),
            'base_total_offline_refunded' => (float)$order->getBaseTotalOfflineRefunded(),
            'total_online_refunded' => (float)$order->getTotalOnlineRefunded(),
            'base_total_online_refunded' => (float)$order->getBaseTotalOnlineRefunded(),
            'grand_total' => (float)$order->getGrandTotal(),
            'base_grand_total' => (float)$order->getBaseGrandTotal(),
            'subtotal' => (float)$order->getSubtotal(),
            'base_subtotal' => (float)$order->getBaseSubtotal(),
            'subtotal_incl_tax' => (float)$order->getSubtotalInclTax(),
            'base_subtotal_incl_tax' => (float)$order->getBaseSubtotalInclTax(),
            'total_paid' => (float)$order->getTotalPaid(),
            'base_total_paid' => (float)$order->getBaseTotalPaid(),
            'total_cancelled' => (float)$order->getTotalCanceled(),
            'base_total_cancelled' => (float)$order->getBaseTotalCanceled(),
            'base_currency_code' => (string)$order->getBaseCurrencyCode(),
            'global_currency_code' => (string)$order->getGlobalCurrencyCode(),
            'order_currency_code' => (string)$order->getOrderCurrencyCode(),
            'shipping' => (float)$order->getShippingAmount(),
            'base_shipping' => (float)$order->getBaseShippingAmount(),
            'shipping_tax' => (float)$order->getShippingTaxAmount(),
            'base_shipping_tax' => (float)$order->getBaseShippingTaxAmount(),
            'shipping_incl_tax' => (float)$order->getShippingInclTax(),
            'base_shipping_incl_tax' => (float)$order->getBaseShippingInclTax(),
            'shipping_invoiced' => (float)$order->getShippingInvoiced(),
            'base_shipping_invoiced' => (float)$order->getBaseShippingInvoiced(),
            'shipping_refunded' => (float)$order->getShippingRefunded(),
            'base_shipping_refunded' => (float)$order->getBaseShippingRefunded(),
            'shipping_canceled' => (float)$order->getShippingCanceled(),
            'base_shipping_canceled' => (float)$order->getBaseShippingCanceled(),
            'tax' => (float)$order->getTaxAmount(),
            'base_tax' => (float)$order->getBaseTaxAmount(),
            'tax_cancelled' => (float)$order->getTaxCanceled(),
            'base_tax_cancelled' => (float)$order->getBaseTaxCanceled(),
            'tax_invoiced' => (float)$order->getTaxInvoiced(),
            'base_tax_invoiced' => (float)$order->getBaseTaxInvoiced(),
            'tax_refunded' => (float)$order->getTaxRefunded(),
            'base_tax_refunded' => (float)$order->getBaseTaxRefunded(),
            'discount' => (float)$order->getDiscountAmount(),
            'base_discount' => (float)$order->getBaseDiscountAmount(),
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
            $fields['payment_method'] = (string)$payment->getMethod();
            $fields['last_transaction_id'] = (string)$payment->getLastTransId();
        }

        $extensionAttrs = $order->getExtensionAttributes();
        if ($extensionAttrs instanceof OrderExtensionInterface) {
            $ext = [];
            $amz = $extensionAttrs->getAmazonOrderReferenceId();
            if (!empty($amz)) {
                $ext['amazon_reference_id'] = (int)$amz->getOrderId();
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
            $result[] = [
                'product' => $this->getProductFieldsById($item->getProductId()),
                'name' => (string)$item->getName(),
                'sku' => (string)$item->getSku(),
                'description' => (string)$item->getDescription(),
                'created_at' => $this->formatDate($item->getCreatedAt()),
                'updated_at' => $this->formatDate($item->getUpdatedAt()),
                'refunded' => (float)$item->getAmountRefunded(),
                'base_refunded' => (float)$item->getBaseAmountRefunded(),
                'base_cost' => (float)$item->getBaseCost(),
                'discount' => (float)$item->getDiscountAmount(),
                'base_discount' => (float)$item->getBaseDiscountAmount(),
                'discount_percent' => (float)$item->getDiscountPercent(),
                'discount_invoiced' => (float)$item->getDiscountInvoiced(),
                'base_discount_invoiced' => (float)$item->getBaseDiscountInvoiced(),
                'discount_refunded' => (float)$item->getDiscountRefunded(),
                'base_discount_refunded' => (float)$item->getBaseDiscountRefunded(),
                'total' => (float)$item->getRowTotal(),
                'base_total' => (float)$item->getBaseRowTotal(),
                'total_incl_tax' => (float)$item->getRowTotalInclTax(),
                'base_total_incl_tax' => (float)$item->getBaseRowTotalInclTax(),
                'price' => (float)$item->getPrice(),
                'base_price' => (float)$item->getBasePrice(),
                'original_price' => (float)$item->getOriginalPrice(),
                'base_original_price' => (float)$item->getBaseOriginalPrice(),
                'qty_ordered' => (float)$item->getQtyOrdered(),
                'qty_back_ordered' => (float)$item->getQtyBackordered(),
                'qty_refunded' => (float)$item->getQtyRefunded(),
                'qty_returned' => (float)$item->getQtyReturned(),
                'qty_cancelled' => (float)$item->getQtyCanceled(),
                'qty_shipped' => (float)$item->getQtyShipped(),
                'gty_invoiced' => (float)$item->getQtyInvoiced(),
                'tax' => (float)$item->getTaxAmount(),
                'base_tax' => (float)$item->getBaseTaxAmount(),
                'is_free_shipping' => $item->getFreeShipping(),
                'tax_percent' => (float)$item->getTaxPercent(),
                'additional_data' => (string)$item->getAdditionalData(),
            ];
        }
        return $result;
    }

    /**
     * @param int|null $productId
     * @return array|null
     */
    private function getProductFieldsById($productId)
    {
        if (empty($productId)) {
            return null;
        }
        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return $this->getProductFields($product);
    }

    /**
     * @param ProductInterface|Product $product
     * @return array
     */
    private function getProductFields($product): array
    {
        return [
            'id' => $product->getId(),
            'type' => (string)$product->getTypeId(),
            'name' => (string)$product->getName(),
            'sku' => (string)$product->getSku(),
            'url' => (string)$product->getProductUrl(),
            'is_virtual' => $product->getIsVirtual(),
            'image_url' => $this->imageHelper->init(
                $product,
                'product_base_image'
            )->setImageFile($product->getImage())->getUrl(),
            'price' => (float)$product->getPrice(),
            'minimal_price' => (float)$product->getMinimalPrice(),
            'updated_at' => $this->formatDate($product->getUpdatedAt()),
            'created_at' => $this->formatDate($product->getCreatedAt()),
        ];
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
