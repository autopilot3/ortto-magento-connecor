<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Setup;

use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Setup\SchemaInterface as Schema;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class InstallSchema implements InstallSchemaInterface
{
    private AutopilotLoggerInterface $logger;

    public function __construct(AutopilotLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        try {
            $this->logger->info("Installing " . Schema::TABLE_SYNC_JOBS);
            Installer::setupJobsTable($setup);
            $this->logger->info("Installing " . Schema::TABLE_CRON_CHECKPOINT);
            Installer::setupCronJobCheckpointTable($setup);
        } catch (Zend_Db_Exception $e) {
            $this->logger->error($e, "Failed to install database schema");
        }
        $setup->endSetup();
    }
}
