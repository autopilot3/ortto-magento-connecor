<?php

namespace Autopilot\AP3Connector\Model\ResourceModel\SyncJob;

use Autopilot\AP3Connector\Model\ResourceModel\SyncJob as ResourceModel;
use Autopilot\AP3Connector\Model\SyncJob as Model;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'autopilot_sync_jobs_collection';
    protected $_idFieldName = "id";

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
