<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\ListCustomerResponseInterface;

interface OrttoCustomerRepositoryInterface
{
    const ANONYMOUS = 'anonymous';
    const ANONYMOUS_CUSTOMER_ID = 0;

    /**
     * @param ConfigScopeInterface $scope
     * @param bool $newsletter Enables checking newsletter subscription status
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @param array $data
     * @return ListCustomerResponseInterface
     */
    public function getList(
        ConfigScopeInterface $scope,
        bool $newsletter,
        bool $crossStore,
        int $page,
        string $checkpoint,
        int $pageSize,
        array $data = []
    );

    /**
     * Returns the list of customers by IDs. The returned array is keyed by customer ID.
     * In case any customer was not found, the value for the key will be null.
     * @param ConfigScopeInterface $scope
     * @param bool $newsletter Enables checking newsletter subscription status
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param int[] $customerIds
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListCustomerResponseInterface
     */
    public function getByIds(
        ConfigScopeInterface $scope,
        bool $newsletter,
        bool $crossStore,
        array $customerIds,
        array $data = []
    );

    /**
     * Returns a customer by ID.
     * @param ConfigScopeInterface $scope
     * @param bool $newsletter Enables checking newsletter subscription status
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param int $customerId
     * @param array $data
     * @return \Ortto\Connector\Api\Data\OrttoCustomerInterface
     */
    public function getById(
        ConfigScopeInterface $scope,
        bool $newsletter,
        bool $crossStore,
        int $customerId,
        array $data = []
    );
}
