<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoProductCategoryInterface
{
    const ID = 'id';
    const NAME = 'name';
    const IMAGE_URL = 'image_url';
    const DESCRIPTION = 'description';
    const PRODUCTS_COUNT = 'products_count';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

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
    * Set products count
    *
    * @param int $productsCount
    * @return $this
    */
    public function setProductsCount($productsCount);

    /**
    * Get products count
    *
    * @return int
    */
    public function getProductsCount();

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
}
