<?php


namespace Autopilot\AP3Connector\Setup;

use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
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

    /**
     * @inheritDoc
     * @throws Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.0.2') >= 0) {
            $this->logger->info("Setting up " . Config::TABLE_SYNC_JOBS . ' table');
            Installer::setupJobsTable($installer);
        }
        $installer->endSetup();
    }
}
