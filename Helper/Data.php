<?php

namespace Autopilot\AP3Connector\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{

    const XML_PATH_BASE_URL = "autopilot/general/base_url";
    const XML_PATH_CLIENT_ID = "autopilot/general/client_id";

    private string $baseURL = "https://magento-integration-api.autopilotapp.com";
    private string $clientID = "mgqQkvCJWDFnxJTgQwfVuYEdQRWVAywE";

    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->_request = $context->getRequest();
    }

    /**
     * @return string
     */
    public function getBaseURL(): string
    {
        $url = $this->scopeConfig->getValue(self::XML_PATH_BASE_URL);
        if (empty($url)) {
            return $this->baseURL;
        }
        return rtrim($url, ' /');
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        $clientID = $this->scopeConfig->getValue(self::XML_PATH_CLIENT_ID);
        if (empty($clientID)) {
            return $this->clientID;
        }
        return $clientID;
    }
}
