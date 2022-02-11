<?php


namespace Autopilot\AP3Connector\Setup;

use Autopilot\AP3Connector\Helper\Config;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class Installer
{

    /**
     * @throws Zend_Db_Exception
     */
    public static function setupJobsTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(Config::TABLE_SYNC_JOBS)) {
            $connection = $setup->getConnection();
            $table = $connection->newTable(Config::TABLE_SYNC_JOBS)->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Job Id'
            )->addColumn(
                'scope_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Scope ID'
            )->addColumn(
                'scope_type',
                Table::TYPE_TEXT,
                24,
                ['nullable' => false],
                'Scope Type'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                24,
                ['nullable' => false],
                'Job status'
            )->addColumn(
                'created_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Created at'
            )->addColumn(
                'finished_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Finished at'
            )->addColumn(
                'count',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => 0, 'nullable' => false],
                'Job Id'
            )->addColumn(
                'error',
                Table::TYPE_TEXT,
                1024,
                [],
                'Error'
            );
            $connection->createTable($table);
        }
    }
}
