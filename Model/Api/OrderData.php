<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Api;

use Ortto\Connector\Helper\Config;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderData
{
    private const CANCELLED_AT = 'canceled_at';
    private const COMPLETED_AT = 'completed_at';

    private Data $helper;
    private OrderItemDataFactory $orderItemDataFactory;
    private AddressDataFactory $addressDataFactory;
    private CreditMemoDataFactory $creditMemoDataFactory;
    private ShipmentTrackRepositoryInterface $shipmentTrackRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    private Order $order;

    public function __construct(
        Data $helper,
        OrderItemDataFactory $orderItemDataFactory,
        AddressDataFactory $addressDataFactory,
        CreditMemoDataFactory $creditMemoDataFactory,
        ShipmentTrackRepositoryInterface $shipmentTrackRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->helper = $helper;
        $this->orderItemDataFactory = $orderItemDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->creditMemoDataFactory = $creditMemoDataFactory;
        $this->shipmentTrackRepository = $shipmentTrackRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param OrderInterface|Order $order
     * @return bool
     */
    public function load(OrderInterface $order)
    {
        if (empty($order)) {
            return false;
        }
        $this->order = $order;
        return true;
    }

    /**
     * @param bool $loadRefunds
     * @return array
     */
    public function toArray(bool $loadRefunds = false): array
    {
        $orderId = To::int($this->order->getEntityId());
        $state = (string)$this->order->getState();

        $items = $this->order->getAllVisibleItems();
        $productIds = [];
        foreach ($items as $item) {
            $productIds[] = To::int($item->getProductId());
        }

        $fields = [
            'id' => $orderId,
            'is_virtual' => To::bool($this->order->getIsVirtual()),
            'number' => (string)$this->order->getIncrementId(),
            'status' => (string)$this->order->getStatus(),
            'state' => $state,
            'quantity' => To::float($this->order->getTotalQtyOrdered()),
            'base_quantity' => To::float($this->order->getBaseTotalQtyOrdered()),
            'created_at' => $this->helper->toUTC($this->order->getCreatedAt()),
            'updated_at' => $this->helper->toUTC($this->order->getUpdatedAt()),
            'ip_address' => $this->order->getRemoteIp(),
            'total_due' => To::float($this->order->getTotalDue()),
            'base_total_due' => To::float($this->order->getBaseTotalDue()),
            'total_invoiced' => To::float($this->order->getTotalInvoiced()),
            'base_total_invoiced' => To::float($this->order->getBaseTotalInvoiced()),
            'total_offline_refunded' => To::float($this->order->getTotalOfflineRefunded()),
            'base_total_offline_refunded' => To::float($this->order->getBaseTotalOfflineRefunded()),
            'total_online_refunded' => To::float($this->order->getTotalOnlineRefunded()),
            'base_total_online_refunded' => To::float($this->order->getBaseTotalOnlineRefunded()),
            'grand_total' => To::float($this->order->getGrandTotal()),
            'base_grand_total' => To::float($this->order->getBaseGrandTotal()),
            'subtotal' => To::float($this->order->getSubtotal()),
            'base_subtotal' => To::float($this->order->getBaseSubtotal()),
            'subtotal_incl_tax' => To::float($this->order->getSubtotalInclTax()),
            'base_subtotal_incl_tax' => To::float($this->order->getBaseSubtotalInclTax()),
            'total_paid' => To::float($this->order->getTotalPaid()),
            'base_total_paid' => To::float($this->order->getBaseTotalPaid()),
            'total_cancelled' => To::float($this->order->getTotalCanceled()),
            'base_total_cancelled' => To::float($this->order->getBaseTotalCanceled()),
            'base_currency_code' => (string)$this->order->getBaseCurrencyCode(),
            'global_currency_code' => (string)$this->order->getGlobalCurrencyCode(),
            'order_currency_code' => (string)$this->order->getOrderCurrencyCode(),
            'shipping' => To::float($this->order->getShippingAmount()),
            'base_shipping' => To::float($this->order->getBaseShippingAmount()),
            'shipping_tax' => To::float($this->order->getShippingTaxAmount()),
            'base_shipping_tax' => To::float($this->order->getBaseShippingTaxAmount()),
            'shipping_incl_tax' => To::float($this->order->getShippingInclTax()),
            'base_shipping_incl_tax' => To::float($this->order->getBaseShippingInclTax()),
            'shipping_invoiced' => To::float($this->order->getShippingInvoiced()),
            'base_shipping_invoiced' => To::float($this->order->getBaseShippingInvoiced()),
            'shipping_refunded' => To::float($this->order->getShippingRefunded()),
            'base_shipping_refunded' => To::float($this->order->getBaseShippingRefunded()),
            'shipping_canceled' => To::float($this->order->getShippingCanceled()),
            'base_shipping_canceled' => To::float($this->order->getBaseShippingCanceled()),
            'tax' => To::float($this->order->getTaxAmount()),
            'base_tax' => To::float($this->order->getBaseTaxAmount()),
            'tax_cancelled' => To::float($this->order->getTaxCanceled()),
            'base_tax_cancelled' => To::float($this->order->getBaseTaxCanceled()),
            'tax_invoiced' => To::float($this->order->getTaxInvoiced()),
            'base_tax_invoiced' => To::float($this->order->getBaseTaxInvoiced()),
            'tax_refunded' => To::float($this->order->getTaxRefunded()),
            'base_tax_refunded' => To::float($this->order->getBaseTaxRefunded()),
            'discount' => To::float($this->order->getDiscountAmount()),
            'base_discount' => To::float($this->order->getBaseDiscountAmount()),
            'discount_refunded' => To::float($this->order->getDiscountRefunded()),
            'base_discount_refunded' => To::float($this->order->getBaseDiscountRefunded()),
            'discount_cancelled' => To::float($this->order->getDiscountCanceled()),
            'base_discount_cancelled' => To::float($this->order->getBaseDiscountCanceled()),
            'discount_invoiced' => To::float($this->order->getDiscountInvoiced()),
            'base_discount_invoiced' => To::float($this->order->getBaseDiscountInvoiced()),
            'base_discount_description' => (string)$this->order->getDiscountDescription(),
            'shipping_discount' => To::float($this->order->getShippingDiscountAmount()),
            'base_shipping_discount' => To::float($this->order->getBaseShippingDiscountAmount()),
            // In case they support multiple codes in the future
            // https://support.magento.com/hc/en-us/articles/115004348454-How-many-coupons-can-a-customer-use-in-Adobe-Commerce-
            'discount_codes' => [(string)$this->order->getCouponCode()],
            'protect_code' => (string)$this->order->getProtectCode(),
            'items' => $this->orderItemDataFactory->create()->toArray($items),
            'refunds' => [],
            'carriers' => $this->getShippingCarriers($orderId, $state),
            'cart_id' => To::int($this->order->getQuoteId()),
        ];

        if ($loadRefunds) {
            $refunds = $this->creditMemoDataFactory->create();
            if ($refunds->loadByOrderId($orderId, $productIds)) {
                $fields['refunds'] = $refunds->toArray();
            }
        }

        $dates = $this->getCustomDates($this->order);
        foreach ($dates as $key => $value) {
            $fields[$key] = $value;
        }

        $payment = $this->order->getPayment();
        if ($payment !== null) {
            $fields['payment_method'] = (string)$payment->getMethod();
            $fields['last_transaction_id'] = (string)$payment->getLastTransId();
        }

        $extensionAttrs = $this->order->getExtensionAttributes();
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

        $addresses = $this->order->getAddresses();
        foreach ($addresses as $address) {
            switch ($address->getAddressType()) {
                case "shipping":
                    if (!$this->order->getIsVirtual()) {
                        $shippingAddress = $this->addressDataFactory->create()->toArray($address);
                        $fields[Data::SHIPPING_ADDRESS] = $shippingAddress;
                    }
                    break;
                case "billing":
                    $billingAddress = $this->addressDataFactory->create()->toArray($address);
                    $fields[Data::BILLING_ADDRESS] = $billingAddress;
                    break;
            }
        }

        return $fields;
    }

    private function getShippingCarriers(int $orderId, string $state): array
    {
        if ($state != Order::STATE_COMPLETE) {
            return [];
        }
        $criteria = $this->searchCriteriaBuilder->addFilter(ShipmentTrackInterface::ORDER_ID, $orderId)->create();
        $carriers = $this->shipmentTrackRepository->getList($criteria)->getItems();
        $result = [];
        foreach ($carriers as $carrier) {
            $result[] = [
                'id' => To::int($carrier->getEntityId()),
                'code' => (string)$carrier->getCarrierCode(),
                'title' => (string)$carrier->getTitle(),
                'tracking_number' => (string)$carrier->getTrackNumber(),
                'created_at' => $this->helper->toUTC($carrier->getCreatedAt()),
            ];
        }
        return $result;
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
