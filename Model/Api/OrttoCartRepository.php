<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Api;

use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as QuoteItemsCollectionFactory;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\Data\OrttoCartInterface;
use Ortto\Connector\Api\Data\OrttoCartItemInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Model\Data\OrttoAddressFactory;
use Ortto\Connector\Model\Data\OrttoCartFactory;

class OrttoCartRepository implements \Ortto\Connector\Api\OrttoCartRepositoryInterface
{
    private const PRODUCT_ID = 'product_id';
    private const PARENT_ITEM_ID = 'parent_item_id';
    private const ITEM_ID = 'item_id';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';
    private const DISCOUNT = 'discount_amount';
    private const BASE_DISCOUNT = 'base_discount_amount';
    private const PRICE = 'price';
    private const BASE_PRICE = 'base_price';
    private const ROW_TOTAL = 'row_total';
    private const BASE_ROW_TOTAL = 'base_row_total';
    private const BASE_TAX = 'base_tax_amount';
    private const TAX = 'tax_amount';
    private const TAX_PERCENT = 'tax_percent';
    private const QUANTITY = 'qty';

    private OrttoCartFactory $cartFactory;
    private OrttoLoggerInterface $logger;
    private CartRepositoryInterface $cartRepository;
    private Data $helper;
    private \Ortto\Connector\Model\Data\OrttoCartItemFactory $cartItemFactory;
    private OrttoProductRepository $productRepository;
    private OrttoAddressFactory $addressFactory;
    private UrlInterface $url;
    private QuoteItemsCollectionFactory $quoteItemsCollectionFactory;

    public function __construct(
        OrttoLoggerInterface $logger,
        Data $helper,
        \Ortto\Connector\Model\Data\OrttoCartFactory $cartFactory,
        \Ortto\Connector\Model\Data\OrttoCartItemFactory $cartItemFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        OrttoProductRepository $productRepository,
        \Ortto\Connector\Model\Data\OrttoAddressFactory $addressFactory,
        QuoteItemsCollectionFactory $quoteItemsCollectionFactory,
        UrlInterface $url
    ) {
        $this->cartFactory = $cartFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->cartItemFactory = $cartItemFactory;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->addressFactory = $addressFactory;
        $this->url = $url;
        $this->quoteItemsCollectionFactory = $quoteItemsCollectionFactory;
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(ConfigScopeInterface $scope, int $cartId, array $data = [])
    {
        $cart = $this->cartRepository->get($cartId);
        return $this->convert($scope, $cart);
    }

    /**
     * @param CartInterface|\Magento\Quote\Model\Quote $cart
     * @return OrttoCartInterface
     */
    private function convert(ConfigScopeInterface $scope, $cart)
    {
        $subtotal = To::float($cart->getSubtotal());
        $subtotalWithDiscount = To::float($cart->getSubtotalWithDiscount());
        $baseSubtotal = To::float($cart->getBaseSubtotal());
        $baseSubtotalWithDiscount = To::float($cart->getBaseSubtotalWithDiscount());
        $cartId = To::int($cart->getId());
        $data = $this->cartFactory->create();
        $data->setId($cartId);
        $data->setIpAddress($cart->getRemoteIp());
        $data->setCreatedAt($this->helper->toUTC($cart->getCreatedAt()));
        $data->setUpdatedAt($this->helper->toUTC($cart->getUpdatedAt()));
        $data->setItemsCount(To::int($cart->getItemsCount()));
        $data->setItemsQuantity(To::int($cart->getItemsQty()));
        $data->setCurrencyCode((string)$cart->getGlobalCurrencyCode());
        $data->setBaseCurrencyCode((string)$cart->getBaseCurrencyCode());
        $data->setStoreCurrencyCode((string)$cart->getStoreCurrencyCode());
        // In case they support multiple codes in the future
        // https://support.magento.com/hc/en-us/articles/115004348454-How-many-coupons-can-a-customer-use-in-Adobe-Commerce-
        $data->setDiscountCodes([(string)$cart->getCouponCode()]);
        $data->setItems($this->getItems($scope, $cartId));
        $data->setGrandTotal(To::float($cart->getGrandTotal()));
        $data->setBaseGrandTotal(To::float($cart->getBaseGrandTotal()));
        $data->setSubtotal($subtotal);
        $data->setBaseSubtotal($baseSubtotal);
        $data->setSubtotalWithDiscount($subtotalWithDiscount);
        $data->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);
        $data->setCartUrl($this->url->getUrl('checkout/cart', ['_secure' => true]));
        $data->setCheckoutUrl($this->url->getUrl('checkout', ['_secure' => true]));
        $data->setDiscount($subtotal - $subtotalWithDiscount);
        $data->setBaseDiscount($baseSubtotal - $baseSubtotalWithDiscount);

        $shippingAddress = $cart->getShippingAddress();
        if (!empty($shippingAddress)) {
            $address = $this->convertQuoteAddress($shippingAddress);
            $data->setShippingAddress($address);
            $data->setShipping(To::float($shippingAddress->getShippingAmount()));
            $data->setBaseShipping(To::float($shippingAddress->getBaseShippingAmount()));
            $data->setShippingInclTax(To::float($shippingAddress->getShippingInclTax()));
            $data->setBaseShippingInclTax(To::float($shippingAddress->getBaseShippingTaxAmount()));
            $data->setShippingTax(To::float($shippingAddress->getShippingTaxAmount()));
            $data->setBaseShippingTax(To::float($shippingAddress->getBaseShippingInclTax()));
        }
        $billingAddress = $cart->getBillingAddress();
        if (!empty($billingAddress)) {
            $address = $this->convertQuoteAddress($billingAddress);
            $data->setBillingAddress($address);
            $data->setTax(To::float($billingAddress->getTaxAmount()));
            $data->setBaseTax(To::float($billingAddress->getShippingTaxAmount()));
        }

        return $data;
    }

    /**
     * @param ConfigScopeInterface $scope
     * @param int $cartId
     * @return OrttoCartItemInterface[]
     */
    private function getItems(ConfigScopeInterface $scope, $cartId)
    {
        $collection = $this->quoteItemsCollectionFactory->create();
        $collection->addFieldToSelect("*")
            ->addFieldToFilter(
                CartItemInterface::KEY_QUOTE_ID,
                ["eq" => $cartId]
            )->addFieldToFilter('store_id', ['eq' => $scope->getId()]);

        $productIds = [];
        $cartItems = [];
        $productVariations = [];
        foreach ($collection->getItems() as $item) {
            $productId = To::int($item->getData(self::PRODUCT_ID));
            $productIds[] = $productId;
            // An item wih non-empty parent ID is variation of a configurable product
            // which should not be listed in the items
            if ($patentId = $item->getData(self::PARENT_ITEM_ID)) {
                $productVariations[To::int($patentId)] = $productId;
            } else {
                $cartItems[] = $item;
            }
        }

        $products = $this->productRepository->getByIds($scope, $productIds)->getItems();
        $result = [];
        foreach ($cartItems as $item) {
            $itemId = To::int($item->getData(self::ITEM_ID));
            if (key_exists($itemId, $productVariations)) {
                $productId = $productVariations[$itemId];
            } else {
                $productId = To::int($item->getData(self::PRODUCT_ID));
            }
            $product = $products[$productId];
            if ($product == null) {
                $this->logger->warn("Cart product was not loaded", ['product_id' => $productId]);
                continue;
            }

            $data = $this->cartItemFactory->create();
            $data->setProduct($product);
            $data->setCreatedAt($this->helper->toUTC($item->getData(self::CREATED_AT)));
            $data->setUpdatedAt($this->helper->toUTC($item->getData(self::UPDATED_AT)));
            $data->setDiscount(To::float($item->getData(self::DISCOUNT)));
            $data->setBaseDiscount(To::float($item->getData(self::BASE_DISCOUNT)));
            $data->setPrice(To::float($item->getData(self::PRICE)));
            $data->setBasePrice(To::float($item->getData(self::BASE_PRICE)));
            $data->setRowTotal(To::float($item->getData(self::ROW_TOTAL)));
            $data->setBaseRowTotal(To::float($item->getData(self::BASE_ROW_TOTAL)));
            $data->setBaseTax(To::float($item->getData(self::BASE_TAX)));
            $data->setTax(To::float($item->getData(self::TAX)));
            $data->setTaxPercent(To::float($item->getData(self::TAX_PERCENT)));
            $data->setQuantity(To::float($item->getData(self::QUANTITY)));
            $result[] = $data;
        }
        return $result;
    }

    /**
     * @param Address $address
     * @return \Ortto\Connector\Api\Data\OrttoAddressInterface
     */
    private function convertQuoteAddress($address)
    {
        $data = $this->addressFactory->create();
        $data->setCity((string)$address->getCity());
        $data->setCompany((string)$address->getCompany());
        $data->setFirstName((string)$address->getFirstname());
        $data->setLastName((string)$address->getLastname());
        $data->setMiddleName((string)$address->getMiddlename());
        $data->setPostCode((string)$address->getPostcode());
        $data->setPrefix((string)$address->getPrefix());
        $data->setSuffix((string)$address->getSuffix());
        $data->setRegion((string)$address->getRegion());
        $data->setVat((string)$address->getVatId());
        $data->setPhone((string)$address->getTelephone());
        $data->setType((string)$address->getAddressType());
        $data->setFax((string)$address->getFax());
        $data->setCountryCode((string)$address->getCountry());
        if ($street = $address->getStreetFull()) {
            $data->setStreetLines(explode("\n", $street));
        }
        return $data;
    }
}
