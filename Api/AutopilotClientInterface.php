<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Model\AutopilotException;
use JsonException;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

interface AutopilotClientInterface
{
    /**
     * @param ConfigScopeInterface $scope
     * @param CustomerInterface[] $customers
     * @return ImportResponseInterface|null
     * @throws JsonException|AutopilotException|LocalizedException
     */
    public function importContacts(ConfigScopeInterface $scope, array $customers);

    /**
     * @param ConfigScopeInterface $scope
     * @param OrderInterface[] $orders
     * @return ImportResponseInterface|null
     * @throws JsonException|AutopilotException|LocalizedException
     */
    public function importOrders(ConfigScopeInterface $scope, array $orders);

    /**
     * @param ConfigScopeInterface $scope
     * @param ProductInterface[] $products
     * @return ImportResponseInterface|null
     * @throws JsonException|AutopilotException|LocalizedException
     */
    public function importProducts(ConfigScopeInterface $scope, array $products);
}
