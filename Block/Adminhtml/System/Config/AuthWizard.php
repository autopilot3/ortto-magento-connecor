<?php

namespace Autopilot\AP3Connector\Block\Adminhtml\System\Config;

use Autopilot\AP3Connector\Helper\Data;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class AuthWizard extends Field
{
    protected $_template = 'system/config/auth_wizard.phtml';
    private Data $helper;


    public function __construct(Context $context, Data $helper, array $data = [], ?SecureHtmlRenderer $secureRenderer = null)
    {
        parent::__construct($context, $data, $secureRenderer);
        $this->helper = $helper;
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
        return $this->helper->getAuthenticationURL();
    }

    public function getClientID(): string
    {
        return $this->helper->getClientId();
    }

    public function getScope(): string
    {
        $scope = $this->helper->getScope();
        return $scope->getUniqueName();
    }
}
