<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\ResourceModel\SyncJob;

use Ortto\Connector\Api\Data\SyncJobInterface;
use Ortto\Connector\Model\ResourceModel\SyncJob as ResourceModel;
use Ortto\Connector\Model\SyncJob as Model;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = SyncJobInterface::ENTITY_ID;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
