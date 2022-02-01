<?php

namespace Autopilot\AP3Connector\Block\Adminhtml\System\Config;

use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Helper\ScopeManager;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class AuthWizard extends Field
{
    protected $_template = 'system/config/auth_wizard.phtml';
    private Data $helper;
    private ScopeManager $scopeManager;

    public function __construct(
        Context $context,
        Data $helper,
        ScopeManager $scopeManager,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        parent::__construct($context, $data, $secureRenderer);
        $this->helper = $helper;
        $this->scopeManager = $scopeManager;
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
        return $this->_escaper->escapeHtmlAttr($this->helper->getBaseURL() . "/-/installation/auth");
    }

    public function getClientID(): string
    {
        return $this->_escaper->escapeHtmlAttr($this->helper->getClientId());
    }

    public function getScopeName(): string
    {
        return $this->_escaper->escapeHtmlAttr($this->scopeManager->getCurrentConfigurationScope()->getName());
    }

    public function getScopeCode(): string
    {
        return $this->_escaper->escapeHtmlAttr($this->scopeManager->getCurrentConfigurationScope()->getCode());
    }
}
