<?php
declare(strict_types=1);

namespace Ortto\Connector\Block\Adminhtml\System\Config;

use Ortto\Connector\Api\RoutesInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SyncOrders extends FieldBase
{
    public function __construct(
        Context $context,
        Data $helper,
        ScopeManagerInterface $scopeManager,
        array $data = []
    ) {

        parent::__construct($context, $scopeManager, $helper, $data);
        $this->setTemplate('system/config/sync_orders.phtml');
    }

    public function getAjaxURL(): string
    {
        return $this->_escaper->escapeHtmlAttr($this->getUrl(RoutesInterface::MG_SYNC_ORDERS));
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
