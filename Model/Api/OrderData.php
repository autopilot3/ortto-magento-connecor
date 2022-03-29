<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Model\Api;

use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Helper\To;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

class OrderData
{
    private Data $helper;
    private OrderItemDataFactory $orderItemDataFactory;
    private AddressDataFactory $addressDataFactory;
    private CreditMemoDataFactory $creditMemoDataFactory;

    public function __construct(
        Data $helper,
        OrderItemDataFactory $orderItemDataFactory,
        AddressDataFactory $addressDataFactory,
        CreditMemoDataFactory $creditMemoDataFactory
    ) {
        $this->helper = $helper;
        $this->orderItemDataFactory = $orderItemDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->creditMemoDataFactory = $creditMemoDataFactory;
    }

    /**
     * @param OrderInterface|Order $order
     * @return array
     */
    public function toArray(OrderInterface $order): array
    {
        $orderId = To::int($order->getEntityId());
        $fields = [
            'id' => $orderId,
            'is_virtual' => To::bool($order->getIsVirtual()),
            'number' => (string)$order->getIncrementId(),
            'status' => (string)$order->getStatus(),
            'state' => (string)$order->getState(),
            'quantity' => To::float($order->getTotalQtyOrdered()),
            'base_quantity' => To::float($order->getBaseTotalQtyOrdered()),
            'created_at' => $this->helper->toUTC($order->getCreatedAt()),
            'updated_at' => $this->helper->toUTC($order->getUpdatedAt()),
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
            'items' => $this->orderItemDataFactory->create()->toArray($order->getAllVisibleItems()),
            'refunds' => $this->creditMemoDataFactory->create()->loadByOrderId($orderId),
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
                return $this->helper->toUTC($canceledAt);
            }
        }
        return $this->helper->toUTC($order->getCreatedAt());
    }
}
