<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Setup;

use Autopilot\AP3Connector\Setup\SchemaInterface as Schema;
use Magento\Framework\DB\Adapter\AdapterInterface;
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
        if (!$setup->tableExists(Schema::TABLE_SYNC_JOBS)) {
            $connection = $setup->getConnection();
            $table = $connection->newTable(Schema::TABLE_SYNC_JOBS)->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Job Id'
            )->addColumn(
                'category',
                Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Scope ID'
            )->addColumn(
                'scope_type',
                Table::TYPE_TEXT,
                12,
                ['nullable' => false],
                'Scope Type'
            )->addColumn(
                'scope_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Scope ID'
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
                'started_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Started at'
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
                'Number of records processed'
            )->addColumn(
                'total',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => 0, 'nullable' => false],
                'Number of total records'
            )->addColumn(
                'error',
                Table::TYPE_TEXT,
                1024,
                [],
                'Error'
            )->addColumn(
                'metadata',
                Table::TYPE_TEXT,
                1024,
                ['nullable' => true],
                'Metadata'
            )->addIndex("category_status_index", ["category", "status"])
                ->setComment("Autopilot Cron Job History");
            $connection->createTable($table);
        }
    }

    /**
     * @throws Zend_Db_Exception
     */
    public static function setupCronJobCheckpointTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(Schema::TABLE_CRON_CHECKPOINT)) {
            $connection = $setup->getConnection();
            $table = $connection->newTable(Schema::TABLE_CRON_CHECKPOINT)->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Job Id'
            )->addColumn(
                'category',
                Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Scope ID'
            )->addColumn(
                'scope_type',
                Table::TYPE_TEXT,
                12,
                ['nullable' => false],
                'Scope Type'
            )->addColumn(
                'scope_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Scope ID'
            )->addColumn(
                'last_checked_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Last checked at'
            )->addIndex(
                "scope_category_unique_index",
                ["scope_type", "scope_id", "category"],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
                ->setComment("Autopilot Cron Job Checkpoint");
            $connection->createTable($table);
        }
    }

    /**
     * @throws Zend_Db_Exception
     */
    public static function setupCustomerAttributesTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(Schema::TABLE_CUSTOMER_ATTRIBUTES)) {
            $connection = $setup->getConnection();
            $table = $connection->newTable(Schema::TABLE_CUSTOMER_ATTRIBUTES)->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity id'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Customer Id'
            )->addColumn(
                'contact_id',
                Table::TYPE_TEXT,
                32,
                ['nullable' => false],
                'Autopilot contact Id'
            )->addIndex(
                "customer_contact_unique_index",
                ["customer_id", "contact_id"],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment("Autopilot customer attributes");
            $connection->createTable($table);

            $connection->addForeignKey(
                "autopilot_customer_attributes_fk",
                Schema::TABLE_CUSTOMER_ATTRIBUTES,
                "customer_id",
                'customer_entity',
                'entity_id'
            );
        }
    }
}
