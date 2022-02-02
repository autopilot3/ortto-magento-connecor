<?php

namespace Autopilot\AP3Connector\Helper;

use Autopilot\AP3Connector\Logger\Logger;
use Autopilot\AP3Connector\Model\AutopilotException;
use Exception;
use JsonException;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Client\Curl;

class HTTPClient extends AbstractHelper
{
    private Curl $curl;
    private Data $helper;

    private Logger $logger;
    private ScopeManager $scopeManager;

    public function __construct(Context $context, Curl $curl, Data $helper, ScopeManager $scopeManager, Logger $logger)
    {
        parent::__construct($context);
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

    public function upsertContact(CustomerInterface $customer)
    {
        try {
            $websiteID = $customer->getWebsiteId();
            $storeId = $customer->getStoreId();
            $activeScopes = $this->scopeManager->getActiveScopes($websiteID, $storeId);
            if (empty($activeScopes)) {
                return;
            }
            $url = $this->helper->getBaseURL() . '/magento/contact/merge';

            $data = $this->helper->getCustomerFields($customer);

            foreach ($activeScopes as $scope) {
                $apiKey = $scope->getAPIKey();
                $data['scope'] = $scope->toArray();
                $this->logger->debug("POST " . $url, $storeId, $data);
                $this->postJSON($url, $apiKey, $data);
            }
        } catch (Exception $e) {
            $this->logger->error($e);
        }
    }

    /**
     * @param string $url
     * @param string $apiKey
     * @param array $data
     * @return mixed
     * @throws AutopilotException|JsonException
     */
    private function postJSON(string $url, string $apiKey, array $data)
    {
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->setCredentials($this->helper->getClientId(), $apiKey);
        $payload = json_encode($data, JSON_THROW_ON_ERROR);
        $this->curl->post($url, $payload);
        $status = $this->curl->getStatus();
        $response = $this->curl->getBody();
        if ($status != 200) {
            throw new AutopilotException(
                "HTTP Response Error",
                $url,
                "POST",
                $status,
                [
                    'response' => $response,
                    'status' => $status,
                    'payload' => $data,
                ]
            );
        }
        return json_decode($response, JSON_THROW_ON_ERROR);
    }
}
