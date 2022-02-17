<?php

namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Api\Data\ReadCustomerResultInterface;
use DateTime;

interface CustomerReaderInterface
{
    const PAGE_SIZE = 100;

    /**
     * @param ConfigScopeInterface $scope
     * @param int $page
     * @param DateTime|null $updatedAfter
     * @return ReadCustomerResultInterface
     */
    public function getScopeCustomers(ConfigScopeInterface $scope, int $page, DateTime $updatedAfter = null);
}
