<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\OrttoOrderInterface;

interface OrderApiInterface
{
    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param bool $newsletter Enabled checking newsletter subscription status
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @return \Ortto\Connector\Api\Data\ListOrderResponseInterface
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

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param bool $newsletter Enabled checking newsletter subscription status
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param int $orderId
     * @return \Ortto\Connector\Api\Data\OrttoOrderInterface
     */
    public function getById(
        string $scopeType,
        int $scopeId,
        bool $newsletter,
        bool $crossStore,
        int $orderId
    );
}
