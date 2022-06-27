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
    public function setImageURL($imageURL)
    {
        return $this->setData(self::IMAGE_URL, $imageURL);
    }

    /** @inheirtDoc */
    public function getImageURL()
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
}
