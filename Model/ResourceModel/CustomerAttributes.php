<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Model\ResourceModel;

use Autopilot\AP3Connector\Api\Data\CustomerAttributesInterface;
use Autopilot\AP3Connector\Setup\SchemaInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomerAttributes extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(SchemaInterface::TABLE_CUSTOMER_ATTRIBUTES, CustomerAttributesInterface::ID);
        $this->_useIsObjectNew = true;
    }
}
