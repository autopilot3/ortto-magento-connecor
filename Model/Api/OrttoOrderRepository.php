<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Api;

use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\DataObject;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Sales\Model\Order;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\OrttoCustomerRepositoryInterface;
use Ortto\Connector\Api\OrttoOrderRepositoryInterface;
use Ortto\Connector\Api\OrttoProductRepositoryInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory as AddressCollectionFactory;
use Ortto\Connector\Model\Data\OrttoCustomerFactory;
use Ortto\Connector\Model\Data\OrttoOrderExtensionFactory;
use Ortto\Connector\Model\Data\OrttoRefundFactory;
use Ortto\Connector\Model\Data\OrttoRefundItemFactory;
use Ortto\Connector\Model\Data\OrttoCarrierFactory;
use Ortto\Connector\Model\Data\OrttoOrderFactory;
use Ortto\Connector\Model\Data\ListOrderResponseFactory;
use Ortto\Connector\Model\Data\OrttoAddressFactory;
use Ortto\Connector\Model\Data\OrttoOrderItemFactory;
use Magento\Sales\Api\Data\OrderAddressInterface as AddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Ortto\Connector\Model\Data\OrttoGiftFactory;

class OrttoOrderRepository implements OrttoOrderRepositoryInterface
{
    private const CANCELLED_AT = 'canceled_at';
    private const COMPLETED_AT = 'completed_at';
    private const BILLING_ADDRESS = 'billing';

    private array $countryCache = [];
    private Data $helper;
    private OrttoLogger $logger;
    private ListOrderResponseFactory $listResponseFactory;
    private AddressCollectionFactory $addressCollection;
    private OrttoOrderFactory $orderFactory;
    private OrttoAddressFactory $addressFactory;
    private OrttoCustomerRepositoryInterface $customerRepository;
    private OrttoCustomerFactory $customerFactory;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private OrderRepositoryInterface $orderRepository;
    private SortOrderBuilder $sortOrderBuilder;
    private ShipmentTrackRepositoryInterface $shipmentTrackRepository;
    private OrttoOrderExtensionFactory $extensionFactory;
    private OrttoRefundFactory $refundFactory;
    private OrttoCarrierFactory $carrierFactory;
    private OrttoOrderItemFactory $orderItemFactory;
    private OrttoGiftFactory $giftFactory;
    private CreditmemoRepositoryInterface $creditMemoRepository;
    private OrttoProductRepositoryInterface $productRepository;
    private OrttoRefundItemFactory $refundItemFactory;
    private CountryFactory $countryFactory;

    public function __construct(
        Data $helper,
        OrttoLogger $logger,
        AddressCollectionFactory $addressCollection,
        OrttoCustomerRepositoryInterface $customerRepository,
        ListOrderResponseFactory $listResponseFactory,
        OrttoOrderFactory $orderFactory,
        \Ortto\Connector\Model\Data\OrttoAddressFactory $addressFactory,
        \Ortto\Connector\Model\Data\OrttoCustomerFactory $customerFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Ortto\Connector\Model\Data\OrttoOrderExtensionFactory $extensionFactory,
        \Ortto\Connector\Model\Data\OrttoRefundFactory $refundFactory,
        \Ortto\Connector\Model\Data\OrttoCarrierFactory $carrierFactory,
        \Ortto\Connector\Model\Data\OrttoOrderItemFactory $orderItemFactory,
        \Magento\Sales\Api\ShipmentTrackRepositoryInterface $shipmentTrackRepository,
        \Ortto\Connector\Model\Data\OrttoGiftFactory $giftFactory,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditMemoRepository,
        OrttoProductRepositoryInterface $productRepository,
        \Ortto\Connector\Model\Data\OrttoRefundItemFactory $refundItemFactory,
        CountryFactory $countryFactory
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->listResponseFactory = $listResponseFactory;
        $this->orderFactory = $orderFactory;
        $this->addressFactory = $addressFactory;
        $this->customerRepository = $customerRepository;
        $this->addressCollection = $addressCollection;
        $this->customerFactory = $customerFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->extensionFactory = $extensionFactory;
        $this->refundFactory = $refundFactory;
        $this->carrierFactory = $carrierFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->shipmentTrackRepository = $shipmentTrackRepository;
        $this->giftFactory = $giftFactory;
        $this->creditMemoRepository = $creditMemoRepository;
        $this->productRepository = $productRepository;
        $this->refundItemFactory = $refundItemFactory;
        $this->countryFactory = $countryFactory;
        $this->countryCache = [];
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

        $sortOrder = $this->sortOrderBuilder
            ->setField(OrderInterface::UPDATED_AT)
            ->setDirection(SortOrder::SORT_ASC)->create();

        $this->searchCriteriaBuilder->setPageSize($pageSize)
            ->setCurrentPage($page)
            ->addSortOrder($sortOrder)
            ->addFilter(OrderInterface::STORE_ID, $scope->getId());

        if (!empty($checkpoint)) {
            $this->searchCriteriaBuilder->addFilter(OrderInterface::UPDATED_AT, $checkpoint, 'gteq');
        }

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
        $productIds = [];
        foreach ($orders as $order) {
            if ($customerId = $order->getCustomerId()) {
                $customerIds[] = To::int($customerId);
            }
            $orderIds[] = To::int($order->getEntityId());
            foreach ($order->getItems() as $item) {
                $productIds[] = To::int($item->getProductId());
            }
        }

        // The returned array is keyed by product ID
        $products = $this->productRepository->getByIds($scope, $productIds)->getItems();

        // The returned array is keyed by customer ID
        $customers = $this->customerRepository->getByIds($scope, $customerIds)->getItems();

        // The returned array is keyed by order ID
        $addresses = $this->getOrderAddresses($orderIds);
        $orttoOrders = [];
        foreach ($orders as $order) {
            $orderId = To::int($order->getEntityId());
            $orttoOrder = $this->convertOrder($order, $addresses[$orderId], $products);
            if ($customerId = $order->getCustomerId()) {
                if ($customer = $customers[To::int($customerId)]) {
                    $orttoOrder->setCustomer($customer);
                }
            } else {
                $orttoOrder->setCustomer($this->getAnonymousCustomer($order, $addresses[$orderId]));
            }
            $orttoOrders[] = $orttoOrder;
        }
        $result->setItems($orttoOrders);
        $result->setHasMore($page < $total / $pageSize);
        return $result;
    }

    public function getById(ConfigScopeInterface $scope, int $orderId, array $data = [])
    {
        $order = $this->orderRepository->get($orderId);
        $productIds = [];
        foreach ($order->getItems() as $item) {
            $productIds[] = To::int($item->getProductId());
        }

        // The returned array is keyed by product ID
        $products = $this->productRepository->getByIds($scope, $productIds)->getItems();

        $addresses = $this->getOrderAddresses([$orderId]);
        $data = $this->convertOrder($order, $addresses[$orderId], $products);
        if ($customerId = $order->getCustomerId()) {
            $data->setCustomer($this->customerRepository->getById($scope, To::int($customerId)));
        } else {
            $data->setCustomer($this->getAnonymousCustomer($order, $addresses[$orderId]));
        }
        return $data;
    }


    /**
     * @param int[] $orderIds
     * @return \Ortto\Connector\Api\Data\OrttoAddressInterface[][]
     */
    private function getOrderAddresses(array $orderIds)
    {
        if (empty($orderIds)) {
            return [];
        }
        $columnsToSelect = [
            AddressInterface::ADDRESS_TYPE,
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
        $orderIds = array_unique($orderIds, SORT_NUMERIC);
        $collection = $this->addressCollection->create();
        $collection->addFieldToSelect($columnsToSelect)
            ->addFieldToFilter(AddressInterface::PARENT_ID, ['in' => $orderIds]);

        $addresses = [];
        foreach ($orderIds as $orderId) {
            $addresses[$orderId] = null;
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
     * @param \Ortto\Connector\Api\Data\OrttoProductInterface[] $products
     * @return \Ortto\Connector\Api\Data\OrttoOrderInterface
     */
    private function convertOrder($order, $addresses, $products)
    {
        $data = $this->orderFactory->create();
        $orderId = To::int($order->getData(OrderInterface::ENTITY_ID));
        $data->setId($orderId);
        foreach ($addresses as $address) {
            if ($address->getType() == self::BILLING_ADDRESS) {
                $data->setBillingAddress($address);
            } else {
                $data->setShippingAddress($address);
            }
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
        $data->setTotalRefunded(To::float($order->getTotalRefunded()));
        $data->setBaseTotalRefunded(To::float($order->getBaseTotalRefunded()));
        $data->setSubtotalRefunded(To::float($order->getSubtotalRefunded()));
        $data->setBaseSubtotalRefunded(To::float($order->getBaseSubtotalRefunded()));
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
        $data->setDiscount(abs(To::float($order->getDiscountAmount())));
        $data->setBaseDiscount(To::float($order->getBaseDiscountAmount()));
        $data->setDiscountDescription((string)$order->getDiscountDescription());
        $data->setDiscountRefunded(abs(To::float($order->getDiscountRefunded())));
        $data->setBaseDiscountRefunded(abs(To::float($order->getBaseDiscountRefunded())));
        $data->setDiscountInvoiced(abs(To::float($order->getDiscountInvoiced())));
        $data->setBaseDiscountInvoiced(abs(To::float($order->getBaseDiscountInvoiced())));
        $data->setDiscountCancelled(abs(To::float($order->getDiscountCanceled())));
        $data->setBaseDiscountCancelled(abs(To::float($order->getBaseDiscountCanceled())));
        $data->setShippingDiscount(abs(To::float($order->getShippingDiscountAmount())));
        $data->setBaseShippingDiscount(abs(To::float($order->getBaseShippingDiscountAmount())));
        if ($payment = $order->getPayment()) {
            $data->setLastTransactionId((string)$payment->getLastTransId());
            $data->setPaymentMethod((string)$payment->getMethod());
        }
        // In case they support multiple codes in the future
        // https://support.magento.com/hc/en-us/articles/115004348454-How-many-coupons-can-a-customer-use-in-Adobe-Commerce-
        $data->setDiscountCodes([(string)$order->getCouponCode()]);
        $data->setProtectCode((string)$order->getProtectCode());

        $items = $order->getAllItems();
        $orderItems = [];
        $productVariations = [];
        $bundles = [];
        // We need to find the bundles in a separate loop because we cannot rely on the order of the items
        foreach ($items as $item) {
            if ((string)$item->getProductType() == 'bundle') {
                $bundles[$item->getItemId()] = 0.0;
            }
        }

        $anyItemShipped = false;
        foreach ($items as $item) {
            if ($item->getQtyShipped() > 0) {
                $anyItemShipped = true;
            }
            // An item wih non-empty parent ID is variation of a configurable product
            // which should not be listed in the items
            if ($patentId = $item->getParentItemId()) {
                if (array_key_exists($patentId, $bundles)) {
                    // It's not a variant of a configurable product. It's a bundled sub-product.
                    $bundles[$patentId] += abs(To::float($item->getDiscountAmount()));
                    continue;
                }
                $productVariations[To::int($patentId)] = To::int($item->getProductId());
            } else {
                $orderItems[] = $item;
            }
        }

        $data->setItems($this->getOrderItems($orderItems, $productVariations, $products, $bundles));

        if ($data->getTotalRefunded() > 0) {
            $data->setRefunds($this->getRefunds($orderId, $products));
        }

        if ($anyItemShipped) {
            $data->setCarriers($this->getShippingCarriers($orderId));
        }

        $data->setExtension($this->getExtension($order));
        return $data;
    }

    /**
     * @param OrderItemInterface[] $items
     * @param array $productVariations
     * @param \Ortto\Connector\Api\Data\OrttoProductInterface[] $products
     * @param array $bundles
     * @return \Ortto\Connector\Api\Data\OrttoOrderItemInterface[]
     */
    private function getOrderItems($items, $productVariations, $products, $bundles)
    {
        $orderItems = [];
        foreach ($items as $item) {
            $id = $item->getItemId();
            $itemId = To::int($id);
            $data = $this->orderItemFactory->create();
            $data->setId($itemId);
            $productId = To::int($item->getProductId());
            $product = $products[$productId];
            if ($product == null) {
                $this->logger->warn("Ordered product was not loaded", ['product_id' => $productId]);
                continue;
            }
            $data->setProduct($product);
            if (key_exists($itemId, $productVariations)) {
                $variantId = $productVariations[$itemId];
                $data->setVariant($products[$variantId]);
            }
            $data->setIsVirtual(To::bool($item->getIsVirtual()));
            $data->setSku((string)$item->getSku());

            $data->setDescription((string)$item->getDescription());
            $data->setName((string)$item->getName());
            $data->setCreatedAt($this->helper->toUTC($item->getCreatedAt()));
            $data->setUpdatedAt($this->helper->toUTC($item->getUpdatedAt()));
            $data->setRefunded(To::float($item->getAmountRefunded()));
            $data->setBaseRefunded(To::float($item->getBaseAmountRefunded()));
            $data->setBaseCost(To::float($item->getBaseCost()));
            if (array_key_exists($id, $bundles)) {
                $data->setDiscount($bundles[$id]);
            } else {
                $data->setDiscount(abs(To::float($item->getDiscountAmount())));
            }
            $data->setDiscountPercent(To::float($item->getDiscountPercent()));
            $data->setDiscountInvoiced(abs(To::float($item->getDiscountInvoiced())));
            $data->setBaseDiscountInvoiced(abs(To::float($item->getBaseDiscountInvoiced())));
            $data->setBaseDiscount(abs(To::float($item->getBaseDiscountAmount())));
            $data->setDiscountRefunded(abs(To::float($item->getDiscountRefunded())));
            $data->setBaseDiscountRefunded(abs(To::float($item->getBaseDiscountRefunded())));
            $data->setPrice(To::float($item->getPrice()));
            $data->setBasePrice(To::float($item->getBasePrice()));
            $data->setOriginalPrice(To::float($item->getOriginalPrice()));
            $data->setBaseOriginalPrice(To::float($item->getBaseOriginalPrice()));
            $data->setTotal(To::float($item->getRowTotal()));
            $data->setBaseTotal(To::float($item->getBaseRowTotal()));
            $data->setTotalInclTax(To::float($item->getRowTotalInclTax()));
            $data->setBaseTotalInclTax(To::float($item->getBaseRowTotalInclTax()));
            $data->setQtyInvoiced(To::float($item->getQtyInvoiced()));
            $data->setQtyBackOrdered(To::float($item->getQtyBackordered()));
            $data->setQtyCancelled(To::float($item->getQtyCanceled()));
            $data->setQtyOrdered(To::float($item->getQtyOrdered()));
            $data->setQtyRefunded(To::float($item->getQtyRefunded()));
            $data->setQtyReturned(To::float($item->getQtyReturned()));
            $data->setQtyShipped(To::float($item->getQtyShipped()));
            $data->setTax(To::float($item->getTaxAmount()));
            $data->setTaxPercent(To::float($item->getTaxPercent()));
            $data->setBaseTax(To::float($item->getBaseTaxAmount()));
            $data->setIsFreeShipping(To::bool($item->getFreeShipping()));
            $data->setAdditionalData((string)$item->getAdditionalData());
            $data->setStoreId(To::int($item->getStoreId()));
            $orderItems[] = $data;
        }

        return $orderItems;
    }

    /**
     * @param int $orderId
     * @param \Ortto\Connector\Api\Data\OrttoProductInterface[] $products
     * @return \Ortto\Connector\Api\Data\OrttoRefundInterface[]
     */
    private function getRefunds($orderId, $products)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(CreditmemoInterface::ORDER_ID, $orderId)->create();
        $memos = $this->creditMemoRepository->getList($searchCriteria)->getItems();
        if (empty($memos)) {
            return [];
        }
        $refunds = [];
        foreach ($memos as $memo) {
            $refund = $this->refundFactory->create();
            $refund->setId(To::int($memo->getEntityId()));
            $refund->setInvoiceId(To::int($memo->getInvoiceId()));
            $refund->setNumber((string)$memo->getIncrementId());
            $refund->setSubtotal(To::float($memo->getSubtotal()));
            $refund->setBaseSubtotal(To::float($memo->getBaseSubtotal()));
            $refund->setSubtotalInclTax(To::float($memo->getSubtotalInclTax()));
            $refund->setBaseSubtotalInclTax(To::float($memo->getBaseSubtotalInclTax()));
            $refund->setTax(To::float($memo->getTaxAmount()));
            $refund->setBaseTax(To::float($memo->getBaseTaxAmount()));
            $refund->setShipping(To::float($memo->getShippingAmount()));
            $refund->setBaseShipping(To::float($memo->getBaseShippingAmount()));
            $refund->setShippingInclTax(To::float($memo->getShippingInclTax()));
            $refund->setBaseShippingInclTax(To::float($memo->getBaseShippingInclTax()));
            $refund->setGrandTotal(To::float($memo->getGrandTotal()));
            $refund->setBaseGrandTotal(To::float($memo->getBaseGrandTotal()));
            $refund->setAdjustment(To::float($memo->getAdjustment()));
            $refund->setBaseAdjustment(To::float($memo->getBaseAdjustment()));
            $refund->setRefundedAt($this->helper->toUTC($memo->getCreatedAt()));
            $refund->setDiscount(abs(To::float($memo->getDiscountAmount())));
            $items = [];
            foreach ($memo->getItems() as $item) {
                $totalRefunded = To::float($item->getRowTotal());
                if ($totalRefunded == 0) {
                    continue;
                }
                $productId = To::int($item->getProductId());
                $product = $products[$productId];
                if (empty($product)) {
                    $this->logger->warn('Refund product was not loaded', ['product_id' => $productId]);
                    continue;
                }

                $data = $this->refundItemFactory->create();
                $data->setId(To::int($item->getEntityId()));
                $data->setProduct($product);
                $data->setTotalRefunded($totalRefunded);
                $data->setBaseTotalRefunded(To::float($item->getBaseRowTotal()));
                $data->setDiscountRefunded(abs(To::float($item->getDiscountAmount())));
                $data->setBaseDiscountRefunded(abs(To::float($item->getBaseDiscountAmount())));
                $data->setRefundQuantity(To::float($item->getQty()));
                $data->setOrderItemId(TO::int($item->getOrderItemId()));
                $items[] = $data;
            }
            $refund->setItems($items);
            $refunds[] = $refund;
        }
        return $refunds;
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
     * @param \Ortto\Connector\Api\Data\OrttoAddressInterface[] $orderAddresses
     * @return \Ortto\Connector\Api\Data\OrttoCustomerInterface
     */
    private function getAnonymousCustomer($order, $orderAddresses)
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
        $phoneNumber = '';
        foreach ($orderAddresses as $address) {
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
