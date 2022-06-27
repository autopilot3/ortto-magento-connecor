<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoProductInterface
{
    const ID = 'id';
    const IS_VISIBLE = 'is_visible';
    const TYPE = 'type';
    const NAME = 'name';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const SKU = 'sku';
    const URL = 'url';
    const PRICE = 'price';
    const CALCULATED_PRICE = 'calculated_price';
    const MINIMAL_PRICE = 'minimal_price';
    const WEIGHT = 'weight';
    const IMAGE_URL = 'image_url';
    const STOCK = 'stock';
    const STOCKS = 'stocks';
    const CATEGORY_IDS = 'category_ids';
    const PARENTS = 'parents';
    const CHILDREN = 'children';
    const SHORT_DESCRIPTION = 'short_description';
    const DESCRIPTION = 'description';
    const IS_OPTION_REQUIRED = 'is_option_required';
    const CURRENCY_CODE = 'currency_code';
    const LINKS = 'links';

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Set is visible
     *
     * @param bool $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible);

    /**
     * Get is visible
     *
     * @return bool
     */
    public function getIsVisible();

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get type
     *
     * @return string
     */
    public function getType();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Set url
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url);

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Set calculated price
     *
     * @param float $calculatedPrice
     * @return $this
     */
    public function setCalculatedPrice($calculatedPrice);

    /**
     * Get calculated price
     *
     * @return float
     */
    public function getCalculatedPrice();

    /**
     * Set minimal price
     *
     * @param float $minimalPrice
     * @return $this
     */
    public function setMinimalPrice($minimalPrice);

    /**
     * Get minimal price
     *
     * @return float
     */
    public function getMinimalPrice();

    /**
     * Set weight
     *
     * @param float $weight
     * @return $this
     */
    public function setWeight($weight);

    /**
     * Get weight
     *
     * @return float
     */
    public function getWeight();

    /**
     * Set image url
     *
     * @param string $imageUrl
     * @return $this
     */
    public function setImageUrl($imageUrl);

    /**
     * Get image url
     *
     * @return string
     */
    public function getImageUrl();

    /**
     * Set stock
     *
     * @param \Ortto\Connector\Api\Data\OrttoStockItemInterface $stock
     * @return $this
     */
    public function setStock($stock);

    /**
     * Get stock
     *
     * @return \Ortto\Connector\Api\Data\OrttoStockItemInterface
     */
    public function getStock();

    /**
     * Set stocks
     *
     * @param \Ortto\Connector\Api\Data\OrttoStockInterface[] $stocks
     * @return $this
     */
    public function setStocks(array $stocks);

    /**
     * Get stocks
     *
     * @return \Ortto\Connector\Api\Data\OrttoStockInterface[]
     */
    public function getStocks(): array;

    /**
     * Set category ids
     *
     * @param int[] $categoryIds
     * @return $this
     */
    public function setCategoryIds(array $categoryIds);

    /**
     * Get category ids
     *
     * @return int[]
     */
    public function getCategoryIds(): array;

    /**
     * Set parents
     *
     * @param \Ortto\Connector\Api\Data\OrttoProductParentGroupInterface $parents
     * @return $this
     */
    public function setParents($parents);

    /**
     * Get parents
     *
     * @return \Ortto\Connector\Api\Data\OrttoProductParentGroupInterface
     */
    public function getParents();

    /**
     * Set children
     *
     * @param int[] $children
     * @return $this
     */
    public function setChildren(array $children);

    /**
     * Get children
     *
     * @return int[]
     */
    public function getChildren(): array;

    /**
     * Set short description
     *
     * @param string $shortDescription
     * @return $this
     */
    public function setShortDescription($shortDescription);

    /**
     * Get short description
     *
     * @return string
     */
    public function getShortDescription();

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set is option required
     *
     * @param bool $isOptionRequired
     * @return $this
     */
    public function setIsOptionRequired($isOptionRequired);

    /**
     * Get is option required
     *
     * @return bool
     */
    public function getIsOptionRequired();

    /**
     * Set currency code
     *
     * @param string $currencyCode
     * @return $this
     */
    public function setCurrencyCode($currencyCode);

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrencyCode();

    /**
     * Set links
     *
     * @param \Ortto\Connector\Api\Data\OrttoDownloadLinkInterface[] $links
     * @return $this
     */
    public function setLinks(array $links);

    /**
     * Get links
     *
     * @return \Ortto\Connector\Api\Data\OrttoDownloadLinkInterface[]
     */
    public function getLinks(): array;
}
