<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Data;


use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoStoreInterface;
use Ortto\Connector\Helper\To;

class OrttoStore extends DataObject implements OrttoStoreInterface
{

    /** @inheirtDoc */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /** @inheirtDoc */
    public function getId()
    {
        return To::int($this->getData(self::ID));
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return (string)$this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setUrl(string $url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * @inheritDoc
     */
    public function getUrl()
    {
        return (string)$this->getData(self::URL);
    }

    /**
     * @inheritDoc
     */
    public function serializeToArray()
    {
        if ($this == null) {
            return null;
        }
        return [
            self::ID => $this->getId(),
            self::NAME => $this->getName(),
            self::URL => $this->getUrl(),
        ];
    }
}
