<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Api\Data\CustomerDataInterface;
use Autopilot\AP3Connector\Model\AutopilotException;
use JsonException;
use Magento\Framework\Exception\LocalizedException;

interface AutopilotClientInterface
{
    /**
     * @param ConfigScopeInterface $scope
     * @param CustomerDataInterface[] $customers
     * @return ImportContactResponseInterface|null
     * @throws JsonException|AutopilotException|LocalizedException
     */
    public function importContacts(ConfigScopeInterface $scope, array $customers);

    /**
     * @param ConfigScopeInterface $scope
     * @return mixed
     */
    public function updateAccessToken(ConfigScopeInterface $scope);
}
