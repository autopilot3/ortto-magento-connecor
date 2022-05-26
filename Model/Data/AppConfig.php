<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Ortto\Connector\Api\Data\AppConfigInterface;
use Ortto\Connector\Helper\To;
use Magento\Framework\DataObject;

class AppConfig extends DataObject implements AppConfigInterface
{
    /**
     * @inheritDoc
     */
    public function setScopeId(int $scopeId): AppConfigInterface
    {
        return $this->setData(self::SCOPE_ID, $scopeId);
    }

    /**
     * @inheritDoc
     */
    public function getScopeId(): int
    {
        return To::int($this->getData(self::SCOPE_ID));
    }

    /**
     * @inheritDoc
     */
    public function setScopeType(string $scopeType): AppConfigInterface
    {
        return $this->setData(self::SCOPE_TYPE, $scopeType);
    }

    /**
     * @inheritDoc
     */
    public function getScopeType(): string
    {
        return trim((string)$this->getData(self::SCOPE_TYPE));
    }

    /**
     * @inheritDoc
     */
    public function setKeys(array $keys): AppConfigInterface
    {
        return $this->setData(self::KEYS, $keys);
    }

    /**
     * @inheritDoc
     */
    public function getKeys(): array
    {
        $keys = $this->getData(self::KEYS);
        if (empty($keys)) {
            return [];
        }
        return $keys;
    }
}
