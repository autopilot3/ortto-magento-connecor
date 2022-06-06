<?php

namespace Ortto\Connector\Model\ResourceModel;

use Ortto\Connector\Api\Data\OrderAttributesInterface;
use Ortto\Connector\Api\SchemaInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderAttributes extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = SchemaInterface::TABLE_ORDER_ATTRIBUTES . '_model';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(SchemaInterface::TABLE_ORDER_ATTRIBUTES, OrderAttributesInterface::ENTITY_ID);
        $this->_useIsObjectNew = true;
    }
}
