<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\ResourceModel;

use Ortto\Connector\Api\SchemaInterface as Schema;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SyncJob extends AbstractDb
{
    protected $_eventPrefix = 'ortto_sync_jobs';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Schema::TABLE_SYNC_JOBS, 'id');
        $this->_useIsObjectNew = true;
    }
}
