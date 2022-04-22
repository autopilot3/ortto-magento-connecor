<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Service;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\ConfigurationReaderInterface;
use Autopilot\AP3Connector\Api\RoutesInterface;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Helper\To;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\AutopilotException;
use Autopilot\AP3Connector\Model\ImportResponse;
use Autopilot\AP3Connector\Model\Api\ProductDataFactory;
use Autopilot\AP3Connector\Model\Api\CustomerDataFactory;
use Magento\Framework\HTTP\ClientInterface;
use JsonException;

class AutopilotClient implements AutopilotClientInterface
{
    private const CUSTOMERS = 'customers';
    private const PRODUCTS = 'products';

    private ClientInterface $curl;
    private Data $helper;

    private AutopilotLoggerInterface $logger;
    private ConfigurationReaderInterface $config;
    private ProductDataFactory $productDataFactory;
    private CustomerDataFactory $customerDataFactory;

    public function __construct(
        ClientInterface $curl,
        Data $helper,
        AutopilotLoggerInterface $logger,
        ConfigurationReaderInterface $config,
        ProductDataFactory $productDataFactory,
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
    }

    /**
     * @inheirtDoc
     */
    public function importContacts(ConfigScopeInterface $scope, array $customers)
    {
        $url = $this->helper->getAutopilotURL(RoutesInterface::AP_IMPORT_CONTACTS);

        $payload = [];
        foreach ($customers as $customer) {
            $customerData = $this->customerDataFactory->create();
            $customerData->load($customer);
            $data = $customerData->toArray();
            if (empty($data)) {
                continue;
            }
            $payload[] = $data;
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
        $url = $this->helper->getAutopilotURL(RoutesInterface::AP_IMPORT_ORDERS);
        $payload = $this->helper->getCustomerWithOrderFields($orders, $scope);
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
        $url = $this->helper->getAutopilotURL(RoutesInterface::AP_IMPORT_PRODUCTS);
        $payload = [];
        foreach ($products as $product) {
            $productData = $this->productDataFactory->create();
            $productData->load($product);
            $payload[] = $productData->toArray();
        }
        if (empty($payload)) {
            $this->logger->debug("No products to export");
            return new ImportResponse();
        }
        $response = $this->postJSON($url, $scope, [self::PRODUCTS => $payload]);
        return new ImportResponse($response);
    }

    /**
     * @param string $url
     * @param ConfigScopeInterface $scope
     * @param array $request
     * @return array
     * @throws AutopilotException
     * @throws JsonException
     */
    private function postJSON(string $url, ConfigScopeInterface $scope, array $request)
    {
        $this->logger->debug('POST: ' . $url, ['request' => $request]);
        $apiKey = $this->config->getAPIKey($scope->getType(), $scope->getId());
        $this->curl->setCredentials($this->helper->getClientId(), $apiKey);
        $this->curl->addHeader("Content-Type", "application/json");
        $request['scope'] = $scope->toArray();
        $payload = json_encode($request, JSON_THROW_ON_ERROR);
        $this->curl->post($url, $payload);
        $status = To::int($this->curl->getStatus());
        $response = (string)$this->curl->getBody();
        if (empty($response)) {
            $response = "{}";
        }
        $this->logger->debug('POST: ' . $url, ['response' => $response,]);
        if ($status !== 200) {
            throw new AutopilotException($url, "POST", $status, $payload, $response);
        }
        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}
