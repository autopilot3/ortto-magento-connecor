<?php
declare(strict_types=1);

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
    private bool $isExplicit;
    private int $websiteId;
    /** @var int[] */
    private array $storeIds;

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
            $this->websiteId = $id;
            $website = $this->storeManager->getWebsite($this->id);
            $this->name = $website->getName();
            $this->code = $website->getCode();
            $websites = $this->storeManager->getWebsites();
            $this->isExplicit = true;
            $count = 0;
            foreach ($websites as $w) {
                if ($w->getCode() === $this->code) {
                    $count++;
                }
            }
            $stores = $this->storeManager->getStores();
            foreach ($stores as $s) {
                if ((int)$s->getWebsiteId() === $id) {
                    $this->storeIds[] = (int)$s->getId();
                }
            }
        } else {
            $store = $this->storeManager->getStore($this->id);
            $this->websiteId = (int)$store->getWebsiteId();
            $websiteAPIKey = $this->scopeConfig->getValue(
                Config::XML_PATH_API_KEY,
                ScopeInterface::SCOPE_WEBSITE,
                $this->websiteId
            );
            $storeAPIKey = $this->scopeConfig->getValue(Config::XML_PATH_API_KEY, $type, $id);
            $this->isExplicit = $websiteAPIKey !== $storeAPIKey;
            $this->name = $store->getName();
            $this->code = $store->getCode();
            $stores = $this->storeManager->getStores();
            $this->storeIds[] = $id;
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

        // Code is not necessarily unique in Magento
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
        return $this->isExplicit && !empty($this->getAPIKey());
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

    /**
     * @inheirtDoc
     */
    public function equals(ConfigScopeInterface $scope): bool
    {
        return $this->type === $scope->getType() && $this->code === $scope->getCode();
    }

    /**
     * @inheirtDoc
     */
    public function getWebsiteId(): int
    {
        return $this->websiteId;
    }

    /**
     * @inheirtDoc
     */
    public function toString(): string
    {
        return sprintf("%s:%s:%d", $this->type, $this->code, $this->id);
    }

    /**
     * @inheirtDoc
     */
    public function getStoreIds(): array
    {
        return $this->storeIds;
    }
}
