<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Block\Adminhtml\System\Config;

use Autopilot\AP3Connector\Api\RoutesInterface;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SyncProducts extends FieldBase
{
    public function __construct(
        Context $context,
        Data $helper,
        ScopeManagerInterface $scopeManager,
        array $data = []
    ) {

        parent::__construct($context, $scopeManager, $helper, $data);
        $this->setTemplate('system/config/sync_products.phtml');
    }

    public function getAjaxURL(): string
    {
        return $this->_escaper->escapeHtmlAttr($this->getUrl(RoutesInterface::MG_SYNC_PRODUCTS));
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
}
