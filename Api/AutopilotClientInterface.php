<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Api\Data\CustomerOrderInterface;
use Autopilot\AP3Connector\Model\AutopilotException;
use JsonException;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;

interface AutopilotClientInterface
{
    /**
     * @param ConfigScopeInterface $scope
     * @param CustomerInterface[] $customers
     * @return ImportContactResponseInterface|null
     * @throws JsonException|AutopilotException|LocalizedException
     */
    public function importContacts(ConfigScopeInterface $scope, array $customers);


    /**
     * @param ConfigScopeInterface $scope
     * @param CustomerOrderInterface[] $orders
     * @return ImportOrderResponseInterface|null
     * @throws JsonException|AutopilotException|LocalizedException
     */
    public function importOrders(ConfigScopeInterface $scope, array $orders);

    /**
     * @param ConfigScopeInterface $scope
     * @return mixed
     */
    public function updateAccessToken(ConfigScopeInterface $scope);
}
