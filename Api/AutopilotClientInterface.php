<?php

namespace Autopilot\AP3Connector\Api;

use Magento\Customer\Api\Data\CustomerInterface;

interface AutopilotClientInterface
{
    public function upsertContactBackend(CustomerInterface $customer);

    public function updateAccessToken(ConfigScopeInterface $scope);
}
