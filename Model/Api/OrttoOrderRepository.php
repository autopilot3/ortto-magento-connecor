<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Api;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Sales\Model\Order;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\OrttoCustomerRepositoryInterface;
use Ortto\Connector\Api\OrttoOrderRepositoryInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory as AddressCollectionFactory;
use Ortto\Connector\Model\Data\OrttoCustomerFactory;
use Ortto\Connector\Model\Data\OrttoOrderExtensionFactory;
use Ortto\Connector\Model\Data\OrttoRefundFactory;
use Ortto\Connector\Model\Data\OrttoRefundItemFactory;
use Ortto\Connector\Model\Data\OrttoCarrierFactory;
use Ortto\Connector\Model\Data\OrttoOrderFactory;
use Ortto\Connector\Model\Data\ListOrderResponseFactory;
use Ortto\Connector\Model\Data\OrttoAddressFactory;
use Ortto\Connector\Model\Data\OrttoCountryFactory;
use Ortto\Connector\Model\Data\OrttoOrderItemFactory;
use Magento\Sales\Api\Data\OrderAddressInterface as AddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Ortto\Connector\Model\Data\OrttoGiftFactory;

class OrttoOrderRepository implements OrttoOrderRepositoryInterface
{
    private const CANCELLED_AT = 'canceled_at';
    private const COMPLETED_AT = 'completed_at';
    private const BILLING_ADDRESS = 'billing';

    private Data $helper;
    private OrttoLogger $logger;
    private ListOrderResponseFactory $listResponseFactory;
    private OrderCollectionFactory $orderCollection;
    private AddressCollectionFactory $addressCollection;
    private OrttoOrderFactory $orderFactory;
    private OrttoAddressFactory $addressFactory;
    private OrttoCountryFactory $countryFactory;
    private CountryInformationAcquirerInterface $countryRepository;
    private OrttoCustomerRepositoryInterface $customerRepository;
    private OrttoCustomerFactory $customerFactory;
    private GroupRepositoryInterface $groupRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private OrderRepositoryInterface $orderRepository;
    private SortOrderBuilder $sortOrderBuilder;
    private CreditMemoDataFactory $creditMemoDataFactory;
    private ShipmentTrackRepositoryInterface $shipmentTrackRepository;
    private OrttoOrderExtensionFactory $extensionFactory;
    private OrttoRefundFactory $refundFactory;
    private OrttoRefundItemFactory $refundItemFactory;
    private OrttoCarrierFactory $carrierFactory;
    private OrttoOrderItemFactory $orderItemFactory;
    private OrttoGiftFactory $giftFactory;

    public function __construct(
        Data $helper,
        OrttoLogger $logger,
        OrderCollectionFactory $orderCollection,
        AddressCollectionFactory $addressCollection,
        OrttoCustomerRepositoryInterface $customerRepository,
        ListOrderResponseFactory $listResponseFactory,
        OrttoOrderFactory $orderFactory,
        \Ortto\Connector\Model\Data\OrttoAddressFactory $addressFactory,
        \Ortto\Connector\Model\Data\OrttoCountryFactory $countryFactory,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryRepository,
        \Ortto\Connector\Model\Data\OrttoCustomerFactory $customerFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        CreditMemoDataFactory $creditMemoDataFactory,
        \Ortto\Connector\Model\Data\OrttoOrderExtensionFactory $extensionFactory,
        \Ortto\Connector\Model\Data\OrttoRefundFactory $refundFactory,
        \Ortto\Connector\Model\Data\OrttoRefundItemFactory $refundItemFactory,
        \Ortto\Connector\Model\Data\OrttoCarrierFactory $carrierFactory,
        \Ortto\Connector\Model\Data\OrttoOrderItemFactory $orderItemFactory,
        \Magento\Sales\Api\ShipmentTrackRepositoryInterface $shipmentTrackRepository,
        \Ortto\Connector\Model\Data\OrttoGiftFactory $giftFactory
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->listResponseFactory = $listResponseFactory;
        $this->orderCollection = $orderCollection;
        $this->orderFactory = $orderFactory;
        $this->addressFactory = $addressFactory;
        $this->countryFactory = $countryFactory;
        $this->countryRepository = $countryRepository;
        $this->customerRepository = $customerRepository;
        $this->addressCollection = $addressCollection;
        $this->customerFactory = $customerFactory;
        $this->groupRepository = $groupRepository;
        $this->subscriber = $subscriber;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->creditMemoDataFactory = $creditMemoDataFactory;
        $this->extensionFactory = $extensionFactory;
        $this->refundFactory = $refundFactory;
        $this->refundItemFactory = $refundItemFactory;
        $this->carrierFactory = $carrierFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->shipmentTrackRepository = $shipmentTrackRepository;
        $this->giftFactory = $giftFactory;
    }

    /** @inheirtDoc
     */
    public function getList(ConfigScopeInterface $scope, int $page, string $checkpoint, int $pageSize, array $data = [])
    {
        if ($page < 1) {
            $page = 1;
        }
        if ($pageSize == 0) {
            $pageSize = 100;
        }

        $this->searchCriteriaBuilder->setPageSize($pageSize)
            ->setCurrentPage($page)
            ->addFilter(OrderInterface::STORE_ID, $scope->getId());

        if (!empty($checkpoint)) {
            $this->searchCriteriaBuilder->addFilter(OrderInterface::UPDATED_AT, $checkpoint, 'gteq');
        }
        $sortOrder = $this->sortOrderBuilder->setField(OrderInterface::ENTITY_ID)->setDirection(SortOrder::SORT_DESC);
        $this->searchCriteriaBuilder->addSortOrder($sortOrder->create());

        $ordersList = $this->orderRepository->getList($this->searchCriteriaBuilder->create());

        $result = $this->listResponseFactory->create();
        $total = To::int($ordersList->getTotalCount());
        $result->setTotal($total);
        if ($total == 0) {
            return $result;
        }

        $orders = $ordersList->getItems();
        $customerIds = [];
        $orderIds = [];
        foreach ($orders as $order) {
            if ($customerId = $order->getCustomerId()) {
                $customerIds[] = To::int($customerId);
            }
            $orderIds[] = To::int($order->getEntityId());
        }

        // The returned array is keyed by customer ID
        $customers = $this->customerRepository->getByIds($scope, $customerIds)->getCustomers();

        // Addresses array is indexed by order ID (addresses[orderId] = [address1, address2,...]
        $addresses = $this->getOrderAddresses($orderIds);
        $orttoOrders = [];
        foreach ($orders as $order) {
            $orderId = To::int($order->getEntityId());
            $orderAddresses = $addresses[$orderId];
            $orttoOrder = $this->convertOrder($order, $orderAddresses, $scope->getWebsiteId());
            if ($customerId = $order->getCustomerId()) {
                if (array_key_exists($customerId, $customers)) {
                    $orttoOrder->setCustomer($customers[To::int($customerId)]);
                }
            }
            $orttoOrders[] = $orttoOrder;
        }
        $result->setOrders($orttoOrders);
        $result->setHasMore($page < $total / $pageSize);
        return $result;
    }


    /**
     * @param int[] $orderIds
     * @return \Ortto\Connector\Api\Data\OrttoAddressInterface[][]
     */
    private function getOrderAddresses(array $orderIds)
    {
        if (empty($addressIds)) {
            return [];
        }
        $columnsToSelect = [
            AddressInterface::ENTITY_ID,
            AddressInterface::PARENT_ID,
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
        $orderIds = array_unique($orderIds);
        $collection = $this->addressCollection->create();
        $collection->addFieldToSelect($columnsToSelect)
            ->addFieldToFilter(AddressInterface::PARENT_ID, ['in' => $orderIds]);

        $addresses = [];
        foreach ($orderIds as $orderId) {
            $addresses[$orderId] = [];
        }
        foreach ($collection->getItems() as $address) {
            $orderId = To::int($address->getData(AddressInterface::PARENT_ID));
            $addresses[$orderId][] = $this->convertAddress($address);
        }
        return $addresses;
    }


    /**
     * @param OrderInterface $order
     * @param \Ortto\Connector\Api\Data\OrttoAddressInterface[] $addresses
     * @param int $websiteId
     * @return \Ortto\Connector\Api\Data\OrttoOrderInterface
     */
    private function convertOrder($order, $addresses, $websiteId)
    {
        $data = $this->orderFactory->create();
        $orderId = To::int($order->getData(OrderInterface::ENTITY_ID));
        $data->setId($orderId);
        $customerId = $order->getData(OrderInterface::CUSTOMER_ID);
        if (empty($customerId)) {
            $data->setCustomer($this->getAnonymousCustomer($order, $addresses, $websiteId));
        }
        $data->setNumber((string)$order->getData(OrderInterface::INCREMENT_ID));
        $data->setCartId(To::int($order->getData(OrderInterface::QUOTE_ID)));
        $data->setCreatedAt($this->helper->toUTC((string)$order->getData(OrderInterface::CREATED_AT)));
        $data->setUpdatedAt($this->helper->toUTC((string)$order->getData(OrderInterface::UPDATED_AT)));
        $dateExtensions = $this->getCustomDates($order);
        $data->setCanceledAt($dateExtensions[self::CANCELLED_AT]);
        $data->setCompletedAt($dateExtensions[self::COMPLETED_AT]);
        $data->setStatus((string)$order->getStatus());
        $data->setState((string)$order->getState());
        $data->setBaseCurrencyCode((string)$order->getBaseCurrencyCode());
        $data->setGlobalCurrencyCode((string)$order->getGlobalCurrencyCode());
        $data->setOrderCurrencyCode((string)$order->getOrderCurrencyCode());
        $data->setQuantity(To::float($order->getTotalQtyOrdered()));
        $data->setBaseQuantity(To::float($order->getBaseTotalQtyOrdered()));
        $data->setGrandTotal(To::float($order->getGrandTotal()));
        $data->setBaseGrandTotal(To::float($order->getBaseGrandTotal()));
        $data->setTotalDue(To::float($order->getTotalDue()));
        $data->setBaseTotalDue(To::float($order->getBaseTotalDue()));
        $data->setTotalCancelled(To::float($order->getTotalCanceled()));
        $data->setBaseTotalCancelled(To::float($order->getBaseTotalCanceled()));
        $data->setTotalInvoiced(To::float($order->getTotalInvoiced()));
        $data->setBaseTotalInvoiced(To::float($order->getBaseTotalInvoiced()));
        $data->setSubtotal(To::float($order->getSubtotal()));
        $data->setBaseSubtotal(To::float($order->getBaseSubtotal()));
        $data->setBaseSubtotalInclTax(To::float($order->getBaseSubtotalInclTax()));
        $data->setSubtotalInclTax(To::float($order->getSubtotalInclTax()));
        $data->setTotalOfflineRefunded(To::float($order->getTotalOfflineRefunded()));
        $data->setBaseTotalOfflineRefunded(To::float($order->getBaseTotalOfflineRefunded()));
        $data->setBaseTotalOnlineRefunded(To::float($order->getBaseTotalOnlineRefunded()));
        $data->setTotalOnlineRefunded(To::float($order->getTotalOnlineRefunded()));
        $data->setTotalPaid(To::float($order->getTotalPaid()));
        $data->setBaseTotalPaid(To::float($order->getBaseTotalPaid()));
        $data->setIpAddress((string)$order->getRemoteIp());
        $data->setTax(To::float($order->getTaxAmount()));
        $data->setBaseTax(To::float($order->getBaseTaxAmount()));
        $data->setTaxCancelled(To::float($order->getTaxCanceled()));
        $data->setBaseTaxCancelled(To::float($order->getBaseTaxCanceled()));
        $data->setTaxInvoiced(To::float($order->getTaxInvoiced()));
        $data->setBaseTaxInvoiced(To::float($order->getBaseTaxInvoiced()));
        $data->setTaxRefunded(To::float($order->getTaxRefunded()));
        $data->setBaseTaxRefunded(To::float($order->getBaseTaxRefunded()));
        $data->setShipping(To::float($order->getShippingAmount()));
        $data->setBaseShipping(To::float($order->getBaseShippingAmount()));
        $data->setShippingInclTax(To::float($order->getShippingInclTax()));
        $data->setBaseShippingInclTax(To::float($order->getBaseShippingInclTax()));
        $data->setShippingTax(To::float($order->getShippingTaxAmount()));
        $data->setBaseShippingTax(To::float($order->getBaseShippingTaxAmount()));
        $data->setShippingCancelled(To::float($order->getShippingCanceled()));
        $data->setBaseShippingCancelled(To::float($order->getBaseShippingCanceled()));
        $data->setShippingInvoiced(To::float($order->getShippingInvoiced()));
        $data->setBaseShippingInvoiced(To::float($order->getBaseShippingInvoiced()));
        $data->setShippingRefunded(To::float($order->getShippingRefunded()));
        $data->setBaseShippingRefunded(To::float($order->getBaseShippingRefunded()));
        $data->setDiscount(To::float($order->getDiscountAmount()));
        $data->setBaseDiscount(To::float($order->getBaseDiscountAmount()));
        $data->setDiscountDescription((string)$order->getDiscountDescription());
        $data->setDiscountRefunded(To::float($order->getDiscountRefunded()));
        $data->setBaseDiscountRefunded(To::float($order->getBaseDiscountRefunded()));
        $data->setDiscountInvoiced(To::float($order->getDiscountInvoiced()));
        $data->setBaseDiscountInvoiced(To::float($order->getBaseDiscountInvoiced()));
        $data->setDiscountCancelled(To::float($order->getDiscountCanceled()));
        $data->setBaseDiscountCancelled(To::float($order->getBaseDiscountCanceled()));
        $data->setShippingDiscount(To::float($order->getShippingDiscountAmount()));
        $data->setBaseShippingDiscount(To::float($order->getBaseShippingDiscountAmount()));
        if ($payment = $order->getPayment()) {
            $data->setLastTransactionId((string)$payment->getLastTransId());
            $data->setPaymentMethod((string)$payment->getMethod());
        }
        // In case they support multiple codes in the future
        // https://support.magento.com/hc/en-us/articles/115004348454-How-many-coupons-can-a-customer-use-in-Adobe-Commerce-
        $data->setDiscountCodes([(string)$order->getCouponCode()]);
        $data->setProtectCode((string)$order->getProtectCode());
        foreach ($addresses as $address) {
            if ($address->getType() == self::BILLING_ADDRESS) {
                $data->setBillingAddress($address);
            } else {
                $data->setShippingAddress($address);
            }
        }

        $items = $order->getAllVisibleItems();
        $productIds = [];
        $orderItems = [];
        foreach ($items as $item) {
            $productIds[] = To::int($item->getProductId());
            $orderItems[] = $this->getOrderItem($item);
        }
        $data->setItems($orderItems);

        switch ($order->getState()) {
            case Order::STATE_CLOSED:
                $data->setRefunds($this->getRefunds($orderId, $productIds));
                break;
            case Order::STATE_COMPLETE:
                $data->setCarriers($this->getShippingCarriers($orderId));
                break;
        }

        $data->setExtension($this->getExtension($order));
        return $data;
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return \Ortto\Connector\Api\Data\OrttoOrderItemInterface
     */
    private function getOrderItem($orderItem)
    {
        //TODO
        $item = $this->orderItemFactory->create();
        return $item;
    }

    /**
     * @param int $orderId
     * @param int[] $productIds
     * @return \Ortto\Connector\Api\Data\OrttoRefundInterface[]
     */
    private function getRefunds($orderId, $productIds)
    {
        //TODO
        $refunds = $this->refundFactory->create();
        $refunds = $this->creditMemoDataFactory->create();
        if ($refunds->loadByOrderId($orderId, $productIds)) {
            $fields['refunds'] = $refunds->toArray();
        }
        return [$refunds];
    }

    /**
     * @param OrderInterface $order
     * @return \Ortto\Connector\Api\Data\OrttoOrderExtensionInterface
     */
    private function getExtension($order)
    {
        $data = $this->extensionFactory->create();
        $extensionAttrs = $order->getExtensionAttributes();
        if ($extensionAttrs instanceof OrderExtensionInterface) {
            if ($amz = $extensionAttrs->getAmazonOrderReferenceId()) {
                $data->setAmazonReferenceId(To::int($amz->getOrderId()));
            }
            if ($giftMsg = $extensionAttrs->getGiftMessage()) {
                $gift = $this->giftFactory->create();
                $gift->setMessage((string)$giftMsg->getMessage());
                $gift->setSender((string)$giftMsg->getSender());
                $gift->setRecipient((string)$giftMsg->getRecipient());
            }
        }
        return $data;
    }


    /**
     * @param int $orderId
     * @return \Ortto\Connector\Api\Data\OrttoCarrierInterface[]
     */
    private function getShippingCarriers(int $orderId): array
    {
        $criteria = $this->searchCriteriaBuilder->addFilter(ShipmentTrackInterface::ORDER_ID, $orderId)->create();
        $carriers = $this->shipmentTrackRepository->getList($criteria)->getItems();
        $result = [];
        foreach ($carriers as $carrier) {
            $data = $this->carrierFactory->create();
            $data->setId(To::int($carrier->getEntityId()));
            $data->setCode((string)$carrier->getCarrierCode());
            $data->setTitle((string)$carrier->getTitle());
            $data->setTrackingNumber((string)$carrier->getTrackNumber());
            $data->setCreatedAt($this->helper->toUTC($carrier->getCreatedAt()));
            $result[] = $data;
        }
        return $result;
    }

    /**
     * @param OrderInterface $order
     * @param \Ortto\Connector\Api\Data\OrttoAddressInterface[] $addresses
     * @param int $websiteId
     * @return \Ortto\Connector\Api\Data\OrttoCustomerInterface
     */
    private function getAnonymousCustomer($order, $addresses, $websiteId)
    {
        $data = $this->customerFactory->create();
        $email = (string)$order->getCustomerEmail();
        $data->setId(OrttoCustomerRepositoryInterface::ANONYMOUS_CUSTOMER_ID);
        $data->setPrefix((string)$order->getCustomerPrefix());
        $data->setFirstName((string)$order->getCustomerFirstname());
        $data->setMiddleName((string)$order->getCustomerMiddlename());
        $data->setLastName((string)$order->getCustomerLastname());
        $data->setSuffix((string)$order->getCustomerSuffix());
        $data->setIpAddress((string)$order->getRemoteIp());
        $data->setGender($this->helper->getGenderLabel($order->getCustomerGender()));
        $data->setEmail($email);
        $data->setDateOfBirth($this->helper->toUTC($order->getCustomerDob()));
        $data->setCreatedAt($this->helper->toUTC($order->getCreatedAt()));
        // Set customer's updated at to order's created at
        $data->setUpdatedAt($this->helper->toUTC($order->getCreatedAt()));

        if ($groupId = $order->getCustomerGroupId()) {
            try {
                if ($group = $this->groupRepository->getById($groupId)) {
                    $data->setGroup(($group->getCode()));
                }
            } catch (NoSuchEntityException|LocalizedException $e) {
                $this->logger->error($e, 'Failed to fetch anonymous customer group details');
            }
        }
        if (!empty($email)) {
            $sub = $this->subscriber->loadBySubscriberEmail($email, $websiteId);
            $data->setIsSubscribed($sub->isSubscribed());
        }

        $phoneNumber = '';
        foreach ($addresses as $address) {
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
        $data->setType((string)$address->getData(AddressInterface::ADDRESS_TYPE));
        $data->setPhone((string)$address->getData(AddressInterface::TELEPHONE));
        $data->setFax((string)$address->getData(AddressInterface::FAX));
        if ($street = $address->getData(AddressInterface::STREET)) {
            $data->setStreetLines(explode("\n", $street));
        }
        $data->setCountry($this->extractCountry($address));
        return $data;
    }

    /**
     * @param DataObject $address
     * @return \Ortto\Connector\Api\Data\OrttoCountryInterface
     */
    private function extractCountry($address)
    {
        $data = $this->countryFactory->create();
        $countryId = (string)$address->getData(AddressInterface::COUNTRY_ID);
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

    private function getCustomDates(OrderInterface $order): array
    {
        $dates = [
            self::COMPLETED_AT => Config::EMPTY_DATE_TIME,
            self::CANCELLED_AT => Config::EMPTY_DATE_TIME,
        ];
        switch ((string)$order->getState()) {
            case Order::STATE_CANCELED:
                $attr = $order->getExtensionAttributes();
                if (!empty($attr)) {
                    $date = $attr->getOrttoCanceledAt();
                    if (!empty($date)) {
                        $dates[self::CANCELLED_AT] = $this->helper->toUTC($date);
                    } else {
                        $dates[self::CANCELLED_AT] = $this->helper->toUTC($order->getCreatedAt());
                    }
                }
                break;
            case Order::STATE_COMPLETE:
                $attr = $order->getExtensionAttributes();
                if (!empty($attr)) {
                    $date = $attr->getOrttoCompletedAt();
                    if (!empty($date)) {
                        $dates[self::COMPLETED_AT] = $this->helper->toUTC($date);
                    }
                }
                break;
        }
        return $dates;
    }
}
