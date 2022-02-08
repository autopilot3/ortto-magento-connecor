<?php


namespace Autopilot\AP3Connector\API;

use Autopilot\AP3Connector\API\Data\CouponInterface;
use Autopilot\AP3Connector\Logger\Logger;

class CouponRepository implements CouponRepositoryInterface
{

    private Logger $logger;

    public function __construct(Logger $logger)
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
