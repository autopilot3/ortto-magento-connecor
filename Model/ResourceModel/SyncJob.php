<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\ResourceModel;

use Ortto\Connector\Api\Data\SyncJobInterface;
use Ortto\Connector\Api\SchemaInterface;
use Ortto\Connector\Api\SchemaInterface as Schema;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SyncJob extends AbstractDb
{
    protected $_eventPrefix = SchemaInterface::TABLE_SYNC_JOBS . '_model';
    protected $_idFieldName = SyncJobInterface::ENTITY_ID;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Schema::TABLE_SYNC_JOBS, SyncJobInterface::ENTITY_ID);
        //$this->_useIsObjectNew = true;
    }
}
