<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Model\Api;

use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Helper\To;
use Autopilot\AP3Connector\Logger\Logger;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\JsonConverter;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

class CartData
{
    /** @var CartInterface|Quote $cart */
    private $cart;

    private Data $helper;
    private Logger $logger;
    private CartRepositoryInterface $cartRepository;
    private ProductDataFactory $productDataFactory;
    private array $items;
    private AddressDataFactory $addressDataFactory;

    public function __construct(
        Data $helper,
        CartRepositoryInterface $cartRepository,
        Logger $logger,
        ProductDataFactory $productDataFactory,
        AddressDataFactory $addressDataFactory
    ) {
        $this->items = [];
        $this->helper = $helper;
        $this->logger = $logger;
        $this->cartRepository = $cartRepository;
        $this->productDataFactory = $productDataFactory;
        $this->addressDataFactory = $addressDataFactory;
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
     * @return void
     */
    public function load($cart)
    {
        $this->cart = $cart;
        $this->loadItems();
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
            'id' => To::int($this->cart->getId()),
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
            // cart_url and checkout_url are added in the Checkout block
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
        return JsonConverter::convert($this->toArray());
    }

    private function loadItems()
    {
        $items = $this->cart->getAllVisibleItems();
        if (empty($items)) {
            return;
        }
        foreach ($items as $item) {
            $this->items[] = $this->getItemArray($item);
        }
    }

    /**
     * @param Quote\Item $item
     * @return array
     */
    private function getItemArray(Quote\Item $item): array
    {
        $product = $this->productDataFactory->create();
        $product->load($item->getProduct());
        return [
            'base_discount' => To::float($item->getBaseDiscountAmount()),
            'base_discount_tax_compensation' => To::float($item->getBaseDiscountTaxCompensationAmount()),
            'base_discount_calculated' => To::float($item->getBaseDiscountCalculationPrice()),
            'discount' => To::float($item->getDiscountAmount()),
            'discount_calculated' => To::float($item->getDiscountCalculationPrice()),
            'discount_tax_compensation' => To::float($item->getDiscountTaxCompensationAmount()),
            'base_price' => To::float($item->getBasePrice()),
            'base_price_incl_tax' => To::float($item->getBasePriceInclTax()),
            'price' => To::float($item->getPrice()),
            'price_incl_tax' => To::float($item->getPriceInclTax()),
            'base_row_total' => To::float($item->getBaseRowTotal()),
            'base_row_total_incl_tax' => To::float($item->getBaseRowTotalInclTax()),
            'row_total' => To::float($item->getRowTotal()),
            'row_total_incl_tax' => To::float($item->getRowTotalInclTax()),
            'row_total_after_discount' => To::float($item->getRowTotalWithDiscount()),
            'base_tax' => To::float($item->getBaseTaxAmount()),
            'base_tax_before_discount' => To::float($item->getBaseTaxBeforeDiscount()),
            'tax' => To::float($item->getTaxAmount()),
            'tax_before_discount' => To::float($item->getTaxBeforeDiscount()),
            'tax_percent' => To::float($item->getTaxPercent()),
            'quantity' => To::float($item->getQty()),
            'product' => $product->toArray(),
        ];
    }
}
