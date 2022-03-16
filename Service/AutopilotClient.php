<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Service;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\ConfigurationReaderInterface;
use Autopilot\AP3Connector\Api\RoutesInterface;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\AutopilotException;
use Autopilot\AP3Connector\Model\ImportResponse;
use Exception;
use JsonException;
use Magento\Framework\HTTP\ClientInterface;

class AutopilotClient implements AutopilotClientInterface
{
    private const CUSTOMERS = 'customers';

    private ClientInterface $curl;
    private Data $helper;

    private AutopilotLoggerInterface $logger;
    private ConfigurationReaderInterface $config;

    public function __construct(
        ClientInterface $curl,
        Data $helper,
        AutopilotLoggerInterface $logger,
        ConfigurationReaderInterface $config
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
    }

    /**
     * @inheirtDoc
     */
    public function importContacts(ConfigScopeInterface $scope, array $customers)
    {
        $url = $this->helper->getAutopilotURL(RoutesInterface::AP_IMPORT_CONTACTS);

        $payload = [];
        foreach ($customers as $customer) {
            $data = $this->helper->getCustomerFields($customer, $scope);
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
    public function updateAccessToken(ConfigScopeInterface $scope)
    {
        try {
            $isActive = $this->config->isActive($scope->getType(), $scope->getId());
            if (!$isActive) {
                return;
            }

            $apiKey = $this->config->getAPIKey($scope->getType(), $scope->getId());
            if (empty($apiKey)) {
                return;
            }

            $this->postJSON(
                $this->helper->getAutopilotURL(RoutesInterface::AP_UPDATE_ACCESS_TOKEN),
                $scope,
                [
                    'scope' => $scope->getCode(),
                    'access_token' => $this->config->getAccessToken($scope->getType(), $scope->getId()),
                ]
            );
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to update access token");
        }
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
        $status = (int)$this->curl->getStatus();
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
