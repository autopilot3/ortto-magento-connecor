<?php

namespace Autopilot\AP3Connector\Model\ResourceModel;

use Autopilot\AP3Connector\Helper\Config;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SyncJob extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'autopilot_sync_jobs_resource_model';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Config::TABLE_SYNC_JOBS, 'id');
        $this->_useIsObjectNew = true;
    }
}
