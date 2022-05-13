<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\ResourceModel;

use Ortto\Connector\Api\CouponRepositoryInterface;
use Ortto\Connector\Api\Data\CouponInterface;
use Ortto\Connector\Logger\OrttoLoggerInterface;

class CouponRepository implements CouponRepositoryInterface
{
    private OrttoLoggerInterface $logger;

    public function __construct(OrttoLoggerInterface $logger)
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
