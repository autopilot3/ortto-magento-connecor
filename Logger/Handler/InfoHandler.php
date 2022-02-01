<?php


namespace Autopilot\AP3Connector\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class InfoHandler extends Base
{
    const FILE_NAME = '/var/log/autopilot/info.log';
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     * @var string
     */
    protected $fileName = self::FILE_NAME;

    protected function write(array $record)
    {
        if ($record['level'] > Logger::INFO) {
            return;
        }
        parent::write($record);
    }
}
