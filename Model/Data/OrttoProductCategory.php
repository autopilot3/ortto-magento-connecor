<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoProductCategoryInterface;
use Ortto\Connector\Helper\To;

class OrttoProductCategory extends DataObject implements OrttoProductCategoryInterface
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

    /** @inheirtDoc */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /** @inheirtDoc */
    public function getName()
    {
        return (string)$this->getData(self::NAME);
    }

    /** @inheirtDoc */
    public function setImageUrl($imageUrl)
    {
        return $this->setData(self::IMAGE_URL, $imageUrl);
    }

    /** @inheirtDoc */
    public function getImageUrl()
    {
        return (string)$this->getData(self::IMAGE_URL);
    }

    /** @inheirtDoc */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /** @inheirtDoc */
    public function getDescription()
    {
        return (string)$this->getData(self::DESCRIPTION);
    }

    /** @inheirtDoc */
    public function setProductsCount($productsCount)
    {
        return $this->setData(self::PRODUCTS_COUNT, $productsCount);
    }

    /** @inheirtDoc */
    public function getProductsCount()
    {
        return To::int($this->getData(self::PRODUCTS_COUNT));
    }

    /** @inheirtDoc */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /** @inheirtDoc */
    public function getCreatedAt()
    {
        return (string)$this->getData(self::CREATED_AT);
    }

    /** @inheirtDoc */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /** @inheirtDoc */
    public function getUpdatedAt()
    {
        return (string)$this->getData(self::UPDATED_AT);
    }

    /** @inheirtDoc */
    public function serializeToArray()
    {
        if ($this == null) {
            return null;
        }
        $result=[];
        $result[self::ID] = $this->getId();
        $result[self::NAME] = $this->getName();
        $result[self::IMAGE_URL] = $this->getImageUrl();
        $result[self::DESCRIPTION] = $this->getDescription();
        $result[self::PRODUCTS_COUNT] = $this->getProductsCount();
        $result[self::CREATED_AT] = $this->getCreatedAt();
        $result[self::UPDATED_AT] = $this->getUpdatedAt();
        return $result;
    }
}
