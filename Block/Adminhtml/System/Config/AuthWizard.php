<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Block\Adminhtml\System\Config;

use Autopilot\AP3Connector\Api\RoutesInterface;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class AuthWizard extends FieldBase
{
    private Data $helper;

    public function __construct(
        Context $context,
        Data $helper,
        ScopeManagerInterface $scopeManager,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        parent::__construct($context, $scopeManager, $helper, $data, $secureRenderer);
        $this->setTemplate('system/config/auth_wizard.phtml');
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

    public function getAjaxURL(): string
    {
        return $this->_escaper->escapeHtmlAttr($this->helper->getAutopilotURL(RoutesInterface::AP_AUTHENTICATE));
    }
}
