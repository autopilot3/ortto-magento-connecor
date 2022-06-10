<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Api;

use Ortto\Connector\Api\OrttoSerializerInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

class CartData
{
    /** @var CartInterface|Quote $cart */
    private $cart;

    private Data $helper;
    private OrttoLogger $logger;
    private CartRepositoryInterface $cartRepository;
    private array $items;
    private AddressDataFactory $addressDataFactory;
    private UrlInterface $url;
    private CartItemDataFactory $cartItemDataFactory;
    private OrttoSerializerInterface $serializer;

    public function __construct(
        Data $helper,
        CartRepositoryInterface $cartRepository,
        OrttoLogger $logger,
        AddressDataFactory $addressDataFactory,
        UrlInterface $url,
        CartItemDataFactory $cartItemDataFactory,
        OrttoSerializerInterface $serializer
    ) {
        $this->items = [];
        $this->helper = $helper;
        $this->logger = $logger;
        $this->cartRepository = $cartRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->url = $url;
        $this->cartItemDataFactory = $cartItemDataFactory;
        $this->serializer = $serializer;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function loadById(int $id)
    {
        try {
            /** @var CartInterface|Quote $cart $cart */
            $cart = $this->cartRepository->get($id);
            $this->load($cart);
            return true;
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e, sprintf("Cart ID %d could not be found.", $id));
            return false;
        }
    }

    /**
     * @param CartInterface|Quote $cart
     * @return bool
     */
    public function load($cart)
    {
        if ($cart == null) {
            return false;
        }
        $this->cart = $cart;
        $this->loadItems();
        return true;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if (empty($this->cart)) {
            return [];
        }
        $fields = [
            'id' => To::int($this->cart->getEntityId()),
            'created_at' => $this->helper->toUTC($this->cart->getCreatedAt()),
            'updated_at' => $this->helper->toUTC($this->cart->getUpdatedAt()),
            'ip_address' => $this->cart->getRemoteIp(),
            'items_quantity' => To::int($this->cart->getItemsQty()),
            'currency_code' => (string)$this->cart->getData('quote_currency_code'),
            'base_currency_code' => (string)$this->cart->getBaseCurrencyCode(),
            'store_currency_code' => (string)$this->cart->getData('store_currency_code'),
            'grand_total' => To::float($this->cart->getGrandTotal()),
            'base_grand_total' => To::float($this->cart->getBaseGrandTotal()),
            'coupon_code' => (string)$this->cart->getCouponCode(),
            'subtotal' => To::float($this->cart->getSubtotal()),
            'base_subtotal' => To::float($this->cart->getBaseSubtotal()),
            'subtotal_with_discount' => To::float($this->cart->getSubtotalWithDiscount()),
            'base_subtotal_with_discount' => To::float($this->cart->getBaseSubtotalWithDiscount()),
            'items' => $this->items,
            'cart_url' => $this->url->getUrl('checkout/cart', ['_secure' => true]),
            'checkout_url' => $this->url->getUrl('checkout', ['_secure' => true]),
            'items_count' => To::int($this->cart->getItemsCount()),
        ];

        $shippingAddress = $this->cart->getShippingAddress();
        if (!empty($shippingAddress)) {
            $address = $this->addressDataFactory->create();
            $fields[CustomerData::SHIPPING_ADDRESS] = $address->toArray($shippingAddress);
            $fields['shipping'] = To::float($shippingAddress->getShippingAmount());
            $fields['shipping_tax'] = To::float($shippingAddress->getShippingTaxAmount());
            $fields['base_shipping'] = To::float($shippingAddress->getBaseShippingAmount());
            $fields['base_shipping_tax'] = To::float($shippingAddress->getBaseShippingTaxAmount());
            $fields['shipping_incl_tax'] = To::float($shippingAddress->getShippingInclTax());
            $fields['base_shipping_incl_tax'] = To::float($shippingAddress->getBaseShippingInclTax());
        }
        $billingAddress = $this->cart->getBillingAddress();
        if (!empty($billingAddress)) {
            $address = $this->addressDataFactory->create();
            $fields[CustomerData::BILLING_ADDRESS] = $address->toArray($billingAddress);
            $fields['tax'] = To::float($billingAddress->getTaxAmount());
            $fields['base_tax'] = To::float($billingAddress->getShippingTaxAmount());
        }

        return $fields;
    }

    /**
     * @return string|bool
     */
    public function toJSON()
    {
        return $this->serializer->serializeJson($this->toArray());
    }

    private function loadItems()
    {
        $items = $this->cart->getAllVisibleItems();
        if (empty($items)) {
            return;
        }
        foreach ($items as $item) {
            $itemData = $this->cartItemDataFactory->create();
            $itemData->load($item);
            $this->items[] = $itemData->toArray();
        }
    }
}
