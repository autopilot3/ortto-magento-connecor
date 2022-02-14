<?php


namespace Autopilot\AP3Connector\Setup;

use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Setup\SchemaInterface as Schema;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

class UpgradeSchema implements UpgradeSchemaInterface
{

    private AutopilotLoggerInterface $logger;

    public function __construct(AutopilotLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            try {
                $this->logger->info("Upgrading " . Schema::TABLE_SYNC_JOBS);
                Installer::setupJobsTable($installer);
            } catch (Zend_Db_Exception $e) {
                $this->logger->error($e, "Failed to upgrade schema");
            }
        }
        $installer->endSetup();
    }
}
