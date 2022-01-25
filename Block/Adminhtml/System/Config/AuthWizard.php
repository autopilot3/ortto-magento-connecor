<?php

namespace Autopilot\AP3Connector\Block\Adminhtml\System\Config;

use Autopilot\AP3Connector\Model\Config;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class AuthWizard extends Field
{
    protected $_template = 'system/config/auth_wizard.phtml';

    private Config $config;

    public function __construct(Context $context, Config $config, array $data = [], ?SecureHtmlRenderer $secureRenderer = null)
    {
        parent::__construct($context, $data, $secureRenderer);
        $this->config = $config;
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();
        $label = $originalData['label'];
        $this->addData([
            'button_label' => $label,
        ]);
        return parent::_toHtml();
    }

    public function getAuthenticationURL(): string
    {
        return $this->config->authenticationURL();
    }

    public function getClientID(): string
    {
        return $this->config->clientID();
    }

    public function getScope(): string
    {
        try {
            $websiteID = $this->_request->getParam('website', 0);
            if ($websiteID > 0) {
                $websites = $this->_storeManager->getWebsites();
                $website = $this->_storeManager->getWebsite($websiteID);
                $count = 0;
                $code = $website->getCode();
                foreach ($websites as $w) {
                    if ($w->getCode() === $code) {
                        $count++;
                    }
                }
                if ($count > 1) {
                    return $website->getName() . '::' . $websiteID;
                }
                return $website->getName();
            }
            $storeID = $this->_request->getParam('store', 0);
            if ($storeID > 0) {
                $stores = $this->_storeManager->getStores();
                $count = 0;
                $store = $this->_storeManager->getStore($storeID);
                $code = $store->getCode();
                foreach ($stores as $s) {
                    if ($s->getCode() === $code) {
                        $count++;
                    }
                }
                if ($count > 1) {
                    return $store->getName() . '::' . $storeID;
                }
                return $store->getName();
            }
            return "";
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->_logger->error("Failed to get store/website details", ['Exception' => $e]);
            return "";
        }
    }
}
