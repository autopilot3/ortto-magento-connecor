<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\ListCustomerResponseInterface;
use Ortto\Connector\Api\Data\OrttoCustomerInterface;

interface CustomerApiInterface
{
    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param bool $newsletter Enabled checking newsletter subscription status
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @param bool $anonymous
     * @return ListCustomerResponseInterface
     */
    public function list(
        string $scopeType,
        int $scopeId,
        bool $newsletter,
        bool $crossStore,
        int $page = 1,
        string $checkpoint = '',
        int $pageSize = 100,
        bool $anonymous = false
    );

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param bool $newsletter Enabled checking newsletter subscription status
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param int $customerId
     * @return OrttoCustomerInterface
     */
    public function getById(string $scopeType, int $scopeId, bool $newsletter, bool $crossStore, int $customerId);
}
