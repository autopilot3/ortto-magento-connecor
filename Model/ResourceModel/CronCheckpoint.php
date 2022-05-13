<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\ResourceModel;

use Ortto\Connector\Api\Data\CronCheckpointInterface;
use Ortto\Connector\Api\SchemaInterface as Schema;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CronCheckpoint extends AbstractDb
{
    protected $_eventPrefix = Schema::TABLE_CRON_CHECKPOINT;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Schema::TABLE_CRON_CHECKPOINT, CronCheckpointInterface::ID);
        $this->_useIsObjectNew = true;
    }
}
