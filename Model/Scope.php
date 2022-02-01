<?php


namespace Autopilot\AP3Connector\Model;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Scope
{

    const XML_PATH_ACTIVE = "autopilot/general/active";
    const XML_PATH_API_KEY = "autopilot/general/apikey";

    private string $name;
    private int $id;
    private string $type;
    private string $code;

    private EncryptorInterface $encryptor;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(EncryptorInterface $encryptor, ScopeConfigInterface $scopeConfig, string $type, int $id)
    {
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->id = $id;
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $name
     * @return Scope
     */
    public function setName(string $name): Scope
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $code
     * @return Scope
     */
    public function setCode(string $code): Scope
    {
        $this->code = $code;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'scope' => array(
                'type' => $this->type,
                'id' => $this->id,
                'name' => $this->name,
                'code' => $this->code,
            )
        ];
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ACTIVE, $this->type, $this->id);
    }

    /**
     * @return string
     */
    public function getAPIKey(): string
    {
        $encrypted = trim($this->scopeConfig->getValue(self::XML_PATH_API_KEY, $this->type, $this->id));
        if (empty($encrypted)) {
            return "";
        }
        return $this->encryptor->decrypt($encrypted);
    }
}
