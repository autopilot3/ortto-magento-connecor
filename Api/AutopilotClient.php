<?php

namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Autopilot\AP3Connector\Model\AutopilotException;
use Exception;
use JsonException;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\HTTP\ClientInterface;

class AutopilotClient implements AutopilotClientInterface
{
    private ClientInterface $curl;
    private Data $helper;

    private AutopilotLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;

    public function __construct(
        ClientInterface $curl,
        Data $helper,
        ScopeManagerInterface $scopeManager,
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
        $this->scopeManager = $scopeManager;
    }

    public function upsertContactBackend(CustomerInterface $customer)
    {
        try {
            $websiteID = $customer->getWebsiteId();
            $storeId = $customer->getStoreId();
            $activeScopes = $this->scopeManager->getActiveScopes($websiteID, $storeId);
            if (empty($activeScopes)) {
                return;
            }
            $url = $this->helper->getAutopilotURL('magento/backend/contact/merge');

            $admin = $this->helper->getAdminUserFields();

            foreach ($activeScopes as $scope) {
                $data = $this->helper->getCustomerFields($customer, $scope);
                if (empty($data)) {
                    continue;
                }
                $data['updated_by'] = $admin;
                $apiKey = $scope->getAPIKey();
                $data['scope'] = $scope->toArray();
                $this->postJSON($url, $apiKey, $data);
            }
        } catch (Exception $e) {
            $this->logger->error($e);
        }
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
                $this->helper->getAutopilotURL(Config::UPDATE_ACCESS_TOKEN_ROUTE),
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
     * @return mixed
     * @throws AutopilotException|JsonException
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
        $this->logger->debug('POST: ' . $url, [
            'response' => empty($response) ? '{}' : $response,
        ]);
        if ($status != 200) {
            throw new AutopilotException($url, "POST", $status, $payload, $response);
        }
        return json_decode($response, JSON_THROW_ON_ERROR);
    }
}
