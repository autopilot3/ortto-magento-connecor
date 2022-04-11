<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Block\Catalog\Product;

use Autopilot\AP3Connector\Api\RoutesInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;

class View extends \Magento\Catalog\Block\Product\View
{
    public function getAjaxURL(): string
    {
        return $this->getUrl(RoutesInterface::MG_PRODUCT_VIEW);
    }

    public function getSession(): Session
    {
        return $this->customerSession;
    }
}
