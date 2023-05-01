<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\ListSubscriberResponseInterface;

interface SubscriberApiInterface
{
    /**
     * @param int $storeId
     * @param int $customerId
     * @param string $email
     * @param int $state
     * @param int $page
     * @param int $pageSize
     * @return ListSubscriberResponseInterface
     */
    public function getAll(
        int $storeId = -1,
        int $customerId = -1,
        string $email = '',
        int $state = -1,
        int $page = 1,
        int $pageSize = 100
    );

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @return ListSubscriberResponseInterface
     */
    public function list(
        string $scopeType,
        int $scopeId,
        bool $crossStore,
        int $page = 1,
        string $checkpoint = '',
        int $pageSize = 100
    );

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param string $email
     * @return bool
     */
    public function getStateByEmail(
        string $scopeType,
        int $scopeId,
        bool $crossStore,
        string $email
    );
}
