<?php


namespace Autopilot\AP3Connector\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class ErrorHandler extends Base
{
    public const FILE_NAME = '/var/log/autopilot_error.log';
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::WARNING;

    /**
     * File name
     * @var string
     */
    protected $fileName = self::FILE_NAME;
}
