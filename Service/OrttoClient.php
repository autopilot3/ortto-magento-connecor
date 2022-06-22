<?php
declare(strict_types=1);

namespace Ortto\Connector\Service;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Ortto\Connector\Api\OrttoClientInterface;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\ConfigurationReaderInterface;
use Ortto\Connector\Api\RoutesInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Model\OrttoException;
use Ortto\Connector\Model\ImportResponse;
use Ortto\Connector\Model\Api\ProductDataFactory;
use Ortto\Connector\Model\Api\CategoryDataFactory;
use Ortto\Connector\Model\Api\CustomerDataFactory;
use Magento\Framework\HTTP\ClientInterface;
use JsonException;

class OrttoClient implements OrttoClientInterface
{
    private const CUSTOMERS = 'customers';
    private const PRODUCTS = 'products';
    private const CATEGORIES = 'categories';
    private const ALERTS = 'alerts';

    private ClientInterface $curl;
    private Data $helper;

    private OrttoLoggerInterface $logger;
    private ConfigurationReaderInterface $config;
    private ProductDataFactory $productDataFactory;
    private CustomerDataFactory $customerDataFactory;
    private CategoryDataFactory $categoryDataFactory;

    public function __construct(
        ClientInterface $curl,
        Data $helper,
        OrttoLoggerInterface $logger,
        ConfigurationReaderInterface $config,
        ProductDataFactory $productDataFactory,
        CategoryDataFactory $categoryDataFactory,
        CustomerDataFactory $customerDataFactory
    ) {
        // In Seconds
        $curl->setOption(CURLOPT_TIMEOUT, 10);
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 10);
        // Return the transfer as a string, instead of outputting it directly to STDOUT.
        $curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl = $curl;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->config = $config;
        $this->productDataFactory = $productDataFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->categoryDataFactory = $categoryDataFactory;
    }

    /**
     * @inheirtDoc
     */
    public function importContacts(ConfigScopeInterface $scope, array $customers, bool $storeFront = false)
    {
        $url = $this->helper->getOrttoURL(RoutesInterface::ORTTO_IMPORT_CONTACTS);

        $payload = [];
        foreach ($customers as $customer) {
            $customerData = $this->customerDataFactory->create();
            if (!$customerData->load($customer, $storeFront)) {
                continue;
            }
            $payload[] = $customerData->toArray();
        }
        if (empty($payload)) {
            $this->logger->debug("No customer to export");
            return new ImportResponse();
        }
        $response = $this->postJSON($url, $scope, [self::CUSTOMERS => $payload]);
        return new ImportResponse($response);
    }

    /**
     * @inheirtDoc
     */
    public function importOrders(ConfigScopeInterface $scope, array $orders)
    {
        return $this->importCustomerOrders($scope, $orders, false);
    }

    /**
     * @inheirtDoc
     */
    public function importOrder(ConfigScopeInterface $scope, OrderInterface $order)
    {
        $isModified = false;
        $state = $order->getState();
        if ($state != Order::STATE_CANCELED && $state != Order::STATE_COMPLETE && $state != Order::STATE_CLOSED) {
            if ($created = strtotime((string)$order->getCreatedAt())) {
                if ($updated = strtotime((string)$order->getUpdatedAt())) {
                    $isModified = abs($updated - $created) > 5; // Seconds
                } else {
                    $this->logger->warn("Invalid order update date", ['date' => (string)$order->getUpdatedAt()]);
                }
            } else {
                $this->logger->warn("Invalid order creation date", ['date' => (string)$order->getCreatedAt()]);
            }
        }

        return $this->importCustomerOrders($scope, [$order], $isModified);
    }

    private function importCustomerOrders(ConfigScopeInterface $scope, array $orders, bool $isModified)
    {
        $url = $this->helper->getOrttoURL(RoutesInterface::ORTTO_IMPORT_ORDERS);
        $payload = $this->helper->getCustomerWithOrderFields($orders, $scope, $isModified);
        if (empty($payload)) {
            $this->logger->debug("No order to export");
            return new ImportResponse();
        }
        $response = $this->postJSON($url, $scope, [self::CUSTOMERS => $payload]);
        return new ImportResponse($response);
    }

    /**
     * @inheirtDoc
     */
    public function importProducts(ConfigScopeInterface $scope, array $products)
    {
        $url = $this->helper->getOrttoURL(RoutesInterface::ORTTO_IMPORT_PRODUCTS);
        $productList = [];
        $categoryIDs = [];
        $uniqueCategories = [];
        foreach ($products as $product) {
            $productData = $this->productDataFactory->create();
            if ($productData->load($product, $scope->getId())) {
                $productList[] = $productData->toArray();
            }
            foreach ($product->getCategoryIds() as $cid) {
                $categoryID = To::int($cid);
                if (array_key_exists($categoryID, $categoryIDs)) {
                    continue;
                }
                $categoryData = $this->categoryDataFactory->create();
                if ($categoryData->loadById($categoryID)) {
                    $uniqueCategories[] = $categoryData->toArray();
                    $categoryIDs[$categoryID] = true;
                }
            }
        }
        if (empty($productList)) {
            $this->logger->debug("No products to export");
            return new ImportResponse();
        }
        $response = $this->postJSON($url, $scope, [
            self::PRODUCTS => $productList,
            self::CATEGORIES => $uniqueCategories,
        ]);
        return new ImportResponse($response);
    }

    public function importProductStockAlerts(ConfigScopeInterface $scope, array $alerts)
    {
        $url = $this->helper->getOrttoURL(RoutesInterface::ORTTO_IMPORT_WAITING_ON_STOCK);
        $payload = [];
        foreach ($alerts as $alert) {
            $product = $this->productDataFactory->create();
            if (!$product->loadById(To::int($alert->getProductId()), $scope->getId())) {
                continue;
            }
            $customer = $this->customerDataFactory->create();
            $customer->loadById(To::int($alert->getCustomerId()));
            $payload[] = [
                'product' => $product->toArray(),
                'customer' => $customer->toArray(),
                'date_added' => $this->helper->toUTC($alert->getAddDate()),
            ];
        }
        if (empty($payload)) {
            $this->logger->debug("No stock alerts to export");
            return new ImportResponse();
        }
        $response = $this->postJSON($url, $scope, [self::ALERTS => $payload]);
        return new ImportResponse($response);
    }

    /**
     * @param string $url
     * @param ConfigScopeInterface $scope
     * @param array $request
     * @return array
     * @throws OrttoException
     * @throws JsonException
     */
    private function postJSON(string $url, ConfigScopeInterface $scope, array $request)
    {
        $scopeData = $scope->toArray();
        $logData = [];
        $this->logger->debug('POST: ' . $url, $logData);
        $apiKey = $this->config->getAPIKey($scope->getType(), $scope->getId());
        $this->curl->setCredentials($this->helper->getClientId(), $apiKey);
        $this->curl->addHeader("Content-Type", "application/json");
        $request['scope'] = $scopeData;
        $payload = json_encode($request, JSON_THROW_ON_ERROR);
        $this->curl->post($url, $payload);
        $status = To::int($this->curl->getStatus());
        $response = (string)$this->curl->getBody();
        if (empty($response)) {
            $response = "{}";
        }

        if ($status == 200) {
            if ($this->config->verboseLogging($scope->getType(), $scope->getId())) {
                $this->logger->info(
                    'POST: ' . $url,
                    ['status' => $status, 'request' => $request, 'response' => $response, 'scope' => $scopeData]
                );
            } else {
                $this->logger->debug('POST: ' . $url, ['status' => $status, 'scope' => $scopeData]);
            }
        } else {
            throw new OrttoException($url, "POST", $status, $request, $response);
        }
        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}
