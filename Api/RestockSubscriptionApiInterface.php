<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface RestockSubscriptionApiInterface
{
    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param bool $newsletter Enables checking newsletter subscription status
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @return \Ortto\Connector\Api\Data\ListRestockSubscriptionResponseInterface
     */
    public function list(
        string $scopeType,
        int $scopeId,
        bool $newsletter,
        bool $crossStore,
        int $page = 1,
        string $checkpoint = '',
        int $pageSize = 100
    );
}
