<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface OrttoCustomerRepositoryInterface
{
    const ANONYMOUS = 'anonymous';
    const ANONYMOUS_CUSTOMER_ID = 0;

    /**
     * @param ConfigScopeInterface $scope
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListCustomerResponseInterface
     */
    public function getList(
        ConfigScopeInterface $scope,
        int $page,
        string $checkpoint,
        int $pageSize,
        array $data = []
    );

    /**
     * Returns the list of customers by IDs. The returned array is keyed by customer ID.
     * In case any customer was not found, the value for the key will be null.
     * @param ConfigScopeInterface $scope
     * @param int[] $customerIds
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListCustomerResponseInterface
     */
    public function getByIds(ConfigScopeInterface $scope, array $customerIds, array $data = []);

    /**
     * Returns a customer by ID.
     * @param ConfigScopeInterface $scope
     * @param int $customerId
     * @param array $data
     * @return \Ortto\Connector\Api\Data\OrttoCustomerInterface
     */
    public function getById(ConfigScopeInterface $scope, int $customerId, array $data = []);
}
