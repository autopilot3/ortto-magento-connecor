<?php


namespace Autopilot\AP3Connector\Model;

use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Helper\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Scope implements ConfigScopeInterface
{
    private string $name;
    private int $id;
    private string $type;
    private string $code;

    private EncryptorInterface $encryptor;
    private ScopeConfigInterface $scopeConfig;
    private StoreManagerInterface $storeManager;

    public function __construct(
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->name = 'Unknown';
        $this->type = 'Unknown';
        $this->id = -1;
        $this->code = '';
    }

    /**
     * @param string $type
     * @param int $id
     * @return void
     * @throws LocalizedException|NotFoundException|NoSuchEntityException
     */
    public function load(string $type, int $id)
    {
        $this->type = $type;
        $this->id = $id;
        if ($type === ScopeInterface::SCOPE_WEBSITE) {
            $website = $this->storeManager->getWebsite($this->id);
            $this->name = $website->getName();
            $this->code = $website->getCode();
            $websites = $this->storeManager->getWebsites();
            $count = 0;
            foreach ($websites as $w) {
                if ($w->getCode() === $this->code) {
                    $count++;
                }
            }
        } else {
            $store = $this->storeManager->getStore($this->id);
            $this->name = $store->getName();
            $this->code = $store->getCode();

            $stores = $this->storeManager->getStores();
            $count = 0;
            foreach ($stores as $s) {
                if ($s->getCode() === $this->code) {
                    $count++;
                }
            }
        }

        if (empty(trim($this->code))) {
            throw new NotFoundException(new Phrase("Scope not found", ['type' => $type, 'id' => $id]));
        }

        if ($count > 1) {
            $this->code .= '_' . $this->id;
        }
    }

    /**
     * @inheirtDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheirtDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheirtDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheirtDoc
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @inheirtDoc
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
        ];
    }

    /**
     * @inheirtDoc
     */
    public function isActive(): bool
    {
        return (bool)$this->scopeConfig->getValue(Config::XML_PATH_ACTIVE, $this->type, $this->id);
    }

    /**
     * @inheirtDoc
     */
    public function isConnected(): bool
    {
        return $this->isActive() && !empty($this->getAPIKey());
    }

    /**
     * @inheirtDoc
     */
    public function getAPIKey(): string
    {
        $encrypted = trim($this->scopeConfig->getValue(Config::XML_PATH_API_KEY, $this->type, $this->id));
        if (empty($encrypted)) {
            return "";
        }
        return $this->encryptor->decrypt($encrypted);
    }

    /**
     * @inheirtDoc
     */
    public function isAutoCustomerSyncEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(Config::XML_PATH_SYNC_CUSTOMER_AUTO_ENABLED, $this->type, $this->id);
    }

    /**
     * @inheirtDoc
     */
    public function isNonSubscribedCustomerSyncEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            Config::XML_PATH_SYNC_CUSTOMER_NON_SUBSCRIBED_ENABLED,
            $this->type,
            $this->id
        );
    }

    /**
     * @inheirtDoc
     */
    public function getAccessToken(): string
    {
        $encrypted = trim($this->scopeConfig->getValue(Config::XML_PATH_ACCESS_TOKEN, $this->type, $this->id));
        if (empty($encrypted)) {
            return "";
        }
        return $this->encryptor->decrypt($encrypted);
    }
}
