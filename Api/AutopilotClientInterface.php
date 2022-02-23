<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Model\AutopilotException;
use JsonException;

interface AutopilotClientInterface
{
    /**
     * @param ConfigScopeInterface $scope
     * @param $customers
     * @return ImportContactResponseInterface|null
     * @throws JsonException|AutopilotException
     */
    public function importContacts(ConfigScopeInterface $scope, $customers);

    /**
     * @param ConfigScopeInterface $scope
     * @return mixed
     */
    public function updateAccessToken(ConfigScopeInterface $scope);
}
