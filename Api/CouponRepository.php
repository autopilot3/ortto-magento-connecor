<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Api\Data\CouponInterface;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;

class CouponRepository implements CouponRepositoryInterface
{

    private AutopilotLoggerInterface $logger;

    public function __construct(AutopilotLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function create(CouponInterface $coupon)
    {
        // TODO: Implement me
        $this->logger->info("Title: " . $coupon->getTitle());
    }
}
