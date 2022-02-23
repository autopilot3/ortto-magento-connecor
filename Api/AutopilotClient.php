<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\AutopilotException;
use Autopilot\AP3Connector\Model\ImportContactResponse;
use Exception;
use JsonException;
use Magento\Framework\HTTP\ClientInterface;

class AutopilotClient implements AutopilotClientInterface
{
    private ClientInterface $curl;
    private Data $helper;

    private AutopilotLoggerInterface $logger;

    public function __construct(
        ClientInterface $curl,
        Data $helper,
        AutopilotLoggerInterface $logger
    ) {
        // In Seconds
        $curl->setOption(CURLOPT_TIMEOUT, 10);
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 10);
        // Return the transfer as a string, instead of outputting it directly to STDOUT.
        $curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl = $curl;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @inheirtDoc
     */
    public function importContacts(ConfigScopeInterface $scope, $customers)
    {
        $url = $this->helper->getAutopilotURL(RoutesInterface::AP_IMPORT_CONTACTS);
        $apiKey = $scope->getAPIKey();
        $payload = [];
        foreach ($customers as $customer) {
            $data = $this->helper->getCustomerFields($customer, $scope);
            if (empty($data)) {
                continue;
            }
            $data['scope'] = $scope->toArray();
            $payload[] = $data;
        }
        if (empty($payload)) {
            $this->logger->debug("No customer to export");
            return null;
        }
        $response = $this->postJSON($url, $apiKey, $payload);
        return new ImportContactResponse($response);
    }

    public function updateAccessToken(ConfigScopeInterface $scope)
    {
        try {
            $isActive = $scope->isActive();
            if (!$isActive) {
                return;
            }

            $apiKey = $scope->getAPIKey();
            if (empty($apiKey)) {
                return;
            }

            $this->postJSON(
                $this->helper->getAutopilotURL(RoutesInterface::AP_UPDATE_ACCESS_TOKEN),
                $apiKey,
                [
                    'scope' => $scope->getCode(),
                    'access_token' => $scope->getAccessToken(),
                ]
            );
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to update access token");
        }
    }

    /**
     * @param string $url
     * @param string $apiKey
     * @param array $request
     * @return array
     * @throws AutopilotException
     * @throws JsonException
     */
    private function postJSON(string $url, string $apiKey, array $request)
    {
        $this->logger->debug('POST: ' . $url, ['request' => $request]);
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->setCredentials($this->helper->getClientId(), $apiKey);
        $payload = json_encode($request, JSON_THROW_ON_ERROR);
        $this->curl->post($url, $payload);
        $status = $this->curl->getStatus();
        $response = $this->curl->getBody();
        if (empty($response)) {
            $response = "{}";
        }
        $this->logger->debug('POST: ' . $url, ['response' => $response,]);
        if ($status != 200) {
            throw new AutopilotException($url, "POST", $status, $payload, $response);
        }
        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}
