<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ProductAlert\Model\Stock;
use Ortto\Connector\Model\OrttoException;
use JsonException;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

interface OrttoClientInterface
{
    /**
     * @param ConfigScopeInterface $scope
     * @param CustomerInterface[] $customers
     * @return ImportResponseInterface|null
     * @throws JsonException|OrttoException|LocalizedException
     */
    public function importContacts(ConfigScopeInterface $scope, array $customers);

    /**
     * @param ConfigScopeInterface $scope
     * @param OrderInterface[] $orders
     * @return ImportResponseInterface|null
     * @throws JsonException|OrttoException|LocalizedException
     */
    public function importOrders(ConfigScopeInterface $scope, array $orders);

    /**
     * @param ConfigScopeInterface $scope
     * @param ProductInterface[]|Product[] $products
     * @return ImportResponseInterface|null
     * @throws JsonException|OrttoException|LocalizedException
     */
    public function importProducts(ConfigScopeInterface $scope, array $products);

    /**
     * @param ConfigScopeInterface $scope
     * @param Stock[] $alerts
     * @return ImportResponseInterface|null
     * @throws JsonException|OrttoException|LocalizedException
     */
    public function importProductStockAlerts(ConfigScopeInterface $scope, array $alerts);
}
