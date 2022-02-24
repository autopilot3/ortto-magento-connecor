<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model\ResourceModel;

use Autopilot\AP3Connector\Setup\SchemaInterface as Schema;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CronCheckpoint extends AbstractDb
{
    protected $_eventPrefix = Schema::TABLE_CRON_CHECKPOINT;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Schema::TABLE_CRON_CHECKPOINT, 'id');
        $this->_useIsObjectNew = true;
    }
}
