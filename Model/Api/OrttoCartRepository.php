<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Api;

use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Address;
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

    private OrttoCartFactory $cartFactory;
    private OrttoLoggerInterface $logger;
    private CartRepositoryInterface $cartRepository;
    private Data $helper;
    private \Ortto\Connector\Model\Data\OrttoCartItemFactory $cartItemFactory;
    private OrttoProductRepository $productRepository;
    private OrttoAddressFactory $addressFactory;
    private UrlInterface $url;

    public function __construct(
        OrttoLoggerInterface $logger,
        Data $helper,
        \Ortto\Connector\Model\Data\OrttoCartFactory $cartFactory,
        \Ortto\Connector\Model\Data\OrttoCartItemFactory $cartItemFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        OrttoProductRepository $productRepository,
        \Ortto\Connector\Model\Data\OrttoAddressFactory $addressFactory,
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

        $data = $this->cartFactory->create();
        $data->setId(To::int($cart->getId()));
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
        $data->setItems($this->getItems($scope, $cart));
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
     * @param CartInterface $cart
     * @return OrttoCartItemInterface[]
     */
    private function getItems(ConfigScopeInterface $scope, $cart)
    {
        $items = $cart->getAllVisibleItems();
        $productIds = [];
        foreach ($items as $item) {
            $productIds[] = To::int($item->getProduct()->getId());
        }
        $products = $this->productRepository->getByIds($scope, $productIds);

        $result = [];
        foreach ($items as $item) {
            $data = $this->cartItemFactory->create();
            $productId = To::int($item->getProduct()->getId());
            $data->setProduct($products[$productId]);
            $data->setCreatedAt($this->helper->toUTC($item->getCreatedAt()));
            $data->setUpdatedAt($this->helper->toUTC($item->getUpdatedAt()));
            $data->setDiscount(To::float($item->getDiscountAmount()));
            $data->setDiscountTaxCompensation(To::float($item->getDiscountTaxCompensationAmount()));
            $data->setDiscountCalculated(To::float($item->getDiscountCalculationPrice()));
            $data->setBaseDiscount(To::float($item->getBaseDiscountAmount()));
            $data->setBaseDiscountTaxCompensation(To::float($item->getBaseDiscountTaxCompensationAmount()));
            $data->setBaseDiscountCalculated(To::float($item->getBaseDiscountCalculationPrice()));
            $data->setBasePrice(To::float($item->getBasePrice()));
            $data->setBasePriceInclTax(To::float($item->getBasePriceInclTax()));
            $data->setPrice(To::float($item->getPrice()));
            $data->setPriceInclTax(To::float($item->getPriceInclTax()));
            $data->setBaseRowTotal(To::float($item->getBaseRowTotal()));
            $data->setBaseRowTotalInclTax(To::float($item->getBaseRowTotalInclTax()));
            $data->setRowTotal(To::float($item->getRowTotal()));
            $data->setRowTotalInclTax(To::float($item->getRowTotalInclTax()));
            $data->setRowTotalAfterDiscount(To::float($item->getRowTotalWithDiscount()));
            $data->setBaseTax(To::float($item->getBaseTaxAmount()));
            $data->setBaseTaxBeforeDiscount(To::float($item->getBaseTaxBeforeDiscount()));
            $data->setTax(To::float($item->getTaxAmount()));
            $data->setTaxBeforeDiscount(To::float($item->getTaxBeforeDiscount()));
            $data->setTaxPercent(To::float($item->getTaxPercent()));
            $data->setQuantity(To::float($item->getQty()));
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
        $data->setCountryName((string)$address->getCountry());
        if ($street = $address->getStreetFull()) {
            $data->setStreetLines(explode("\n", $street));
        }
        return $data;
    }
}
