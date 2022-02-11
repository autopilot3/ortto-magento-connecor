<?php

namespace Autopilot\AP3Connector\Model;

use Autopilot\AP3Connector\Model\ResourceModel\SyncJob as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class SyncJob extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'autopilot_sync_jobs_model';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
