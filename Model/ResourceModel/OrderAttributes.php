<?php

namespace Autopilot\AP3Connector\Model\ResourceModel;

use Autopilot\AP3Connector\Api\SchemaInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderAttributes extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'autopilot_order_attributes_resource_model';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(SchemaInterface::TABLE_ORDER_ATTRIBUTES, SchemaInterface::ID_FIELD);
        $this->_useIsObjectNew = true;
    }
}
