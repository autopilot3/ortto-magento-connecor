<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoCarrierInterface
{
    const ID = 'id';
    const CODE = 'code';
    const TITLE = 'title';
    const TRACKING_NUMBER = 'tracking_number';
    const CREATED_AT = 'created_at';

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
    * Set code
    *
    * @param string $code
    * @return $this
    */
    public function setCode($code);

    /**
    * Get code
    *
    * @return string
    */
    public function getCode();

    /**
    * Set title
    *
    * @param string $title
    * @return $this
    */
    public function setTitle($title);

    /**
    * Get title
    *
    * @return string
    */
    public function getTitle();

    /**
    * Set tracking number
    *
    * @param string $trackingNumber
    * @return $this
    */
    public function setTrackingNumber($trackingNumber);

    /**
    * Get tracking number
    *
    * @return string
    */
    public function getTrackingNumber();

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
}
