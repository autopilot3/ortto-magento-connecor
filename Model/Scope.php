<?php
declare(strict_types=1);

namespace Ortto\Connector\Model;

use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Helper\To;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\JsonConverter;

class Scope extends DataObject implements ConfigScopeInterface
{
    /**
     * @inheirtDoc
     */
    public function getId(): int
    {
        return To::int($this->getData(self::ID));
    }

    /**
     * @inheirtDoc
     */
    public function setId(int $id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheirtDoc
     */
    public function getType(): string
    {
        return (string)$this->getData(self::TYPE);
    }

    /**
     * @inheirtDoc
     */
    public function setType(string $type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheirtDoc
     */
    public function getName(): string
    {
        return (string)$this->getData(self::NAME);
    }

    /**
     * @inheirtDoc
     */
    public function setName(string $name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheirtDoc
     */
    public function getCode(): string
    {
        return (string)$this->getData(self::CODE);
    }

    /**
     * @inheirtDoc
     */
    public function setCode(string $code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @inheirtDoc
     */
    public function getBaseURL(): string
    {
        return (string)$this->getData(self::URL);
    }

    /**
     * @inheirtDoc
     */
    public function setBaseURL(string $url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * @inheirtDoc
     */
    public function isExplicitlyConnected(): bool
    {
        return To::bool($this->getData(self::IS_CONNECTED));
    }

    /**
     * @inheirtDoc
     */
    public function setIsExplicitlyConnected(bool $connected)
    {
        return $this->setData(self::IS_CONNECTED, $connected);
    }

    /**
     * @inheirtDoc
     */
    public function getWebsiteId(): int
    {
        return To::int($this->getData(self::WEBSITE_ID));
    }

    /**
     * @inheirtDoc
     */
    public function setWebsiteId(int $id)
    {
        return $this->setData(self::WEBSITE_ID, $id);
    }

    /**
     * @inheirtDoc
     */
    public function toString($format = ''): string
    {
        return sprintf("%s:%s:%d", $this->getType(), $this->getCode(), $this->getId());
    }

    /**
     * @inheirtDoc
     */
    public function getStoreIds(): array
    {
        $storeIds = $this->getData(self::STORE_IDS);
        if (empty($storeIds)) {
            return [];
        }
        return $storeIds;
    }

    /**
     * @inheirtDoc
     */
    public function addStoreId(int $id)
    {
        $storeIds = $this->getStoreIds();
        $storeIds[] = $id;
        $this->setData(self::STORE_IDS, $storeIds);
    }

    /**
     * @inheirtDoc
     */
    public function equals(ConfigScopeInterface $scope): bool
    {
        return $this->getType() === $scope->getType() && $this->getType() === $scope->getCode();
    }

    /**
     * @inheirtDoc
     */
    public function getParent()
    {
        return $this->getData(self::PARENT);
    }

    /**
     * @inheirtDoc
     */
    public function setParent(ConfigScopeInterface $scope)
    {
        return $this->setData(self::PARENT, $scope);
    }

    /**
     * @inheirtDoc
     */
    public function isConnected(): bool
    {
        if ($this->isExplicitlyConnected()) {
            return true;
        }
        $parent = $this->getParent();
        return !empty($parent) && $parent->isExplicitlyConnected();
    }

    /**
     * @inheirtDoc
     */
    public function toJson(array $keys = [])
    {
        $result = $this->toArray($keys);
        return JsonConverter::convert($result);
    }

    /**
     * @inheirtDoc
     */
    public function toArray(array $keys = [])
    {
        if (empty($keys)) {
            $keys = [self::ID, self::CODE, self::NAME, self::TYPE, self::URL, self::STORE_IDS];
        }
        $result = parent::toArray($keys);
        $result[self::PARENT] = null;
        $parent = $this->getParent();
        if (!empty($parent)) {
            $result[self::PARENT] = $parent->toArray();
        }
        return $result;
    }
}
