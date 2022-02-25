<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Api\Data\ReadCustomerResultInterface;
use DateTime;

interface CustomerReaderInterface
{
    const PAGE_SIZE = 100;

    /**
     * @param ConfigScopeInterface $scope
     * @param int $page
     * @param DateTime|null $customerCheckpoint
     * @param DateTime|null $orderCheckpoint
     * @return ReadCustomerResultInterface
     */
    public function getScopeCustomers(
        ConfigScopeInterface $scope,
        int $page,
        ?DateTime $customerCheckpoint = null,
        ?DateTime $orderCheckpoint = null
    );
}
