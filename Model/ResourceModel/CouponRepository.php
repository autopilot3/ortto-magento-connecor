<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model\ResourceModel;

use Autopilot\AP3Connector\Api\CouponRepositoryInterface;
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
        $this->logger->info("Coupon: ", $coupon->toArray());
    }
}
