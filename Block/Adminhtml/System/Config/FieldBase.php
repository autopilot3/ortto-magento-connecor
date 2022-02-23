<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Block\Adminhtml\System\Config;

use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Model\Scope;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

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

    public function getEscapedAttrScopeName(): string
    {
        return $this->_escaper->escapeHtmlAttr($this->scope->getName());
    }

    public function getEscapedAttrScopeCode(): string
    {
        return $this->_escaper->escapeHtmlAttr($this->scope->getCode());
    }

    public function getEscapedAttrScopeType(): string
    {
        return $this->_escaper->escapeHtmlAttr($this->scope->getType());
    }

    public function getEscapedAttrScopeID(): string
    {
        return $this->_escaper->escapeHtmlAttr($this->scope->getId());
    }

    public function getEscapedAttrClientID(): string
    {
        return $this->_escaper->escapeHtmlAttr($this->helper->getClientId());
    }
}
