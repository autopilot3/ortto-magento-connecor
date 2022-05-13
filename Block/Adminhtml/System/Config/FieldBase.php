<?php
declare(strict_types=1);

namespace Ortto\Connector\Block\Adminhtml\System\Config;

use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Model\Scope;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Store\Model\Store;

class FieldBase extends Field
{
    private Scope $scope;
    private Data $helper;

    public function __construct(
        Context $context,
        ScopeManagerInterface $scopeManager,
        Data $helper,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        parent::__construct($context, $data, $secureRenderer);
        $this->scope = $scopeManager->getCurrentConfigurationScope();
        $this->helper = $helper;
    }

    /**
     * @return Scope
     */
    public function getScope(): Scope
    {
        return $this->scope;
    }

    public function getEscapedAttrClientID(): string
    {
        return $this->_escaper->escapeHtmlAttr($this->helper->getClientId());
    }
}
