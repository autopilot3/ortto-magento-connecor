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
     * @param string $imageURL
     * @return $this
     */
    public function setImageURL($imageURL);

    /**
     * Get image url
     *
     * @return string
     */
    public function getImageURL();

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
}
