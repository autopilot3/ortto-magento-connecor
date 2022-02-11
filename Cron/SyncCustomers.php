<?php


namespace Autopilot\AP3Connector\Cron;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;

class SyncCustomers
{
    private AutopilotLoggerInterface $logger;
    private Data $helper;
    private AutopilotClientInterface $autopilotClient;

    public function __construct(
        AutopilotLoggerInterface $logger,
        Data $helper,
        AutopilotClientInterface $autopilotClient
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->autopilotClient = $autopilotClient;
    }

    /**
     * Sync customers with Autopilot
     *
     * @return void
     */
    public function execute(): void
    {
        //$this->logger->info("Cron Works");
    }
}
