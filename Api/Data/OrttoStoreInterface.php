<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoStoreInterface
{
    const ID = 'id';
    const NAME = 'name';
    const URL = 'url';

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId(int $id);

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
    public function setName(string $name);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set URL
     *
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url);

    /**
     * Get URL
     *
     * @return string
     */
    public function getUrl();

    /**
     * Convert object data to array
     *
     * @return array
     */
    public function serializeToArray();
}
