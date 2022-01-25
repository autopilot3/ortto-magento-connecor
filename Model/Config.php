<?php


namespace Autopilot\AP3Connector\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Config
{
    const XML_PATH_ENABLED = "autopilot/general/enabled";
    const XML_PATH_API_KEY = "autopilot/general/apikey";
    const XML_PATH_LOG_ENABLED = "autopilot/general/logs";
    const XML_PATH_AUTHENTICATION_URL = "autopilot/general/authentication_url";
    const XML_PATH_CLIENT_ID = "autopilot/general/client_id";

    private ScopeConfigInterface $config;
    private EncryptorInterface $encryptor;

    private string $authenticationURL = "https://magento-integration-api.autopilotapp.com/-/installation/auth";
    private string $clientID = "mgqQkvCJWDFnxJTgQwfVuYEdQRWVAywE";

    public function __construct(ScopeConfigInterface $config, EncryptorInterface $encryptor)
    {
        $this->config = $config;
        $this->encryptor = $encryptor;
    }

    public function isEnabled(): bool
    {
        return $this->config->getValue(self::XML_PATH_ENABLED);
    }

    public function isLogEnabled(): bool
    {
        return $this->config->getValue(self::XML_PATH_LOG_ENABLED);
    }

    public function apiKey(): string
    {
        $encrypted = trim($this->config->getValue(self::XML_PATH_API_KEY));
        if (empty($encrypted)) {
            return "";
        }
        return $this->encryptor->decrypt($encrypted);
    }

    public function authenticationURL(): string
    {
        $url = $this->config->getValue(self::XML_PATH_AUTHENTICATION_URL);
        if (empty($url)) {
            return $this->authenticationURL;
        }
        return $url;
    }

    public function clientID(): string
    {
        $clientID = $this->config->getValue(self::XML_PATH_CLIENT_ID);
        if (empty($clientID)) {
            return $this->clientID;
        }
        return $clientID;
    }
}
