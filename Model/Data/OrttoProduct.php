<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoProductInterface;
use Ortto\Connector\Helper\To;

class OrttoProduct extends DataObject implements OrttoProductInterface
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
    public function setIsVisible($isVisible)
    {
        return $this->setData(self::IS_VISIBLE, $isVisible);
    }

    /** @inheirtDoc */
    public function getIsVisible()
    {
        return To::bool($this->getData(self::IS_VISIBLE));
    }

    /** @inheirtDoc */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /** @inheirtDoc */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /** @inheirtDoc */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /** @inheirtDoc */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /** @inheirtDoc */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /** @inheirtDoc */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /** @inheirtDoc */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /** @inheirtDoc */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /** @inheirtDoc */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /** @inheirtDoc */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /** @inheirtDoc */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /** @inheirtDoc */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /** @inheirtDoc */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /** @inheirtDoc */
    public function getPrice()
    {
        return To::float($this->getData(self::PRICE));
    }

    /** @inheirtDoc */
    public function setCalculatedPrice($calculatedPrice)
    {
        return $this->setData(self::CALCULATED_PRICE, $calculatedPrice);
    }

    /** @inheirtDoc */
    public function getCalculatedPrice()
    {
        return To::float($this->getData(self::CALCULATED_PRICE));
    }

    /** @inheirtDoc */
    public function setMinimalPrice($minimalPrice)
    {
        return $this->setData(self::MINIMAL_PRICE, $minimalPrice);
    }

    /** @inheirtDoc */
    public function getMinimalPrice()
    {
        return To::float($this->getData(self::MINIMAL_PRICE));
    }

    /** @inheirtDoc */
    public function setWeight($weight)
    {
        return $this->setData(self::WEIGHT, $weight);
    }

    /** @inheirtDoc */
    public function getWeight()
    {
        return To::float($this->getData(self::WEIGHT));
    }

    /** @inheirtDoc */
    public function setImageUrl($imageUrl)
    {
        return $this->setData(self::IMAGE_URL, $imageUrl);
    }

    /** @inheirtDoc */
    public function getImageUrl()
    {
        return $this->getData(self::IMAGE_URL);
    }

    /** @inheirtDoc */
    public function setStock($stock)
    {
        return $this->setData(self::STOCK, $stock);
    }

    /** @inheirtDoc */
    public function getStock()
    {
        return $this->getData(self::STOCK);
    }

    /** @inheirtDoc */
    public function setStocks(array $stocks)
    {
        return $this->setData(self::STOCKS, $stocks);
    }

    /** @inheirtDoc */
    public function getStocks(): array
    {
        return $this->getData(self::STOCKS) ?? [];
    }

    /** @inheirtDoc */
    public function setCategoryIds(array $categoryIds)
    {
        return $this->setData(self::CATEGORY_IDS, $categoryIds);
    }

    /** @inheirtDoc */
    public function getCategoryIds(): array
    {
        return $this->getData(self::CATEGORY_IDS) ?? [];
    }

    /** @inheirtDoc */
    public function setParents($parents)
    {
        return $this->setData(self::PARENTS, $parents);
    }

    /** @inheirtDoc */
    public function getParents()
    {
        return $this->getData(self::PARENTS);
    }

    /** @inheirtDoc */
    public function setChildren(array $children)
    {
        return $this->setData(self::CHILDREN, $children);
    }

    /** @inheirtDoc */
    public function getChildren(): array
    {
        return $this->getData(self::CHILDREN) ?? [];
    }

    /** @inheirtDoc */
    public function setShortDescription($shortDescription)
    {
        return $this->setData(self::SHORT_DESCRIPTION, $shortDescription);
    }

    /** @inheirtDoc */
    public function getShortDescription()
    {
        return $this->getData(self::SHORT_DESCRIPTION);
    }

    /** @inheirtDoc */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /** @inheirtDoc */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /** @inheirtDoc */
    public function setIsOptionRequired($isOptionRequired)
    {
        return $this->setData(self::IS_OPTION_REQUIRED, $isOptionRequired);
    }

    /** @inheirtDoc */
    public function getIsOptionRequired()
    {
        return To::bool($this->getData(self::IS_OPTION_REQUIRED));
    }

    /** @inheirtDoc */
    public function setCurrencyCode($currencyCode)
    {
        return $this->setData(self::CURRENCY_CODE, $currencyCode);
    }

    /** @inheirtDoc */
    public function getCurrencyCode()
    {
        return $this->getData(self::CURRENCY_CODE);
    }

    /** @inheirtDoc */
    public function setLinks(array $links)
    {
        return $this->setData(self::LINKS, $links);
    }

    /** @inheirtDoc */
    public function getLinks(): array
    {
        return $this->getData(self::LINKS) ?? [];
    }
}
