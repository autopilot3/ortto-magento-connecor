<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoSubscriberInterface
{
    const STATUS_SUBSCRIBED = "subscribed"; // 1
    const STATUS_INACTIVE = "inactive"; //2
    const STATUS_UNSUBSCRIBED = "unsubscribed"; // 3
    public const STATUS_UNCONFIRMED = "unconfirmed"; // 4
    public const STATUS_UNKNOWN = "unknown"; // ?

    const SUBSCRIBER_ID = 'subscriber_id';
    const STORE_ID = 'store_id';
    const CHANGE_STATUS_AT = 'change_status_at';
    const CUSTOMER_ID = 'customer_id';
    const SUBSCRIBER_EMAIL = 'subscriber_email';
    const SUBSCRIBER_STATUS = 'subscriber_status';
    const IS_SUBSCRIBED = 'is_subscribed';
    const STATUS = 'status';

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
     * Get store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $id
     * @return $this
     */
    public function setStoreId($id);

    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @param int $id
     * @return $this
     */
    public function setCustomerId($id);

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set status
     *
     * @param int $code
     * @return $this
     */
    public function setStatusCode($code);

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Get status
     *
     * @return int
     */
    public function getStatusCode();

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
     * Get subscription status
     * @return bool
     */
    public function isSubscribed();

    /**
     * Convert object data to array
     *
     * @return array
     */
    public function serializeToArray();
}
