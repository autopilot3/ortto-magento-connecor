<?php

namespace Autopilot\AP3Connector\Model\ResourceModel;

use Autopilot\AP3Connector\Setup\SchemaInterface as Schema;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SyncJob extends AbstractDb
{
    protected $_eventPrefix = 'autopilot_sync_jobs';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Schema::TABLE_SYNC_JOBS, 'id');
        $this->_useIsObjectNew = true;
    }
}
