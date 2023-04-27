<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\ListSubscriberResponseInterface;
use Ortto\Connector\Api\Data\OrttoSubscriberInterface;

interface OrttoSubscriberRepositoryInterface
{
    /**
     * @param int $page
     * @param int $pageSize
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListSubscriberResponseInterface
     */
    public function getAll(
        int $page,
        int $pageSize,
        array $data = []
    );

    /**
     * @param ConfigScopeInterface $scope
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param array $data
     * @return ListSubscriberResponseInterface
     */
    public function getList(
        ConfigScopeInterface $scope,
        int $page,
        string $checkpoint,
        int $pageSize,
        bool $crossStore,
        array $data = []
    );

    /**
     * Returns the list of subscription status by customer IDs. The returned array is keyed by customer ID.
     * In case any subscriber was not found, the value for the key will be false.
     * @param ConfigScopeInterface $scope
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param int[] $customerIds
     * @return bool[]
     */
    public function getStateByCustomerIds(ConfigScopeInterface $scope, bool $crossStore, array $customerIds);

    /**
     * Returns the list of subscription status by email addresses. The returned array is keyed by email address.
     * In case any subscriber was not found, the value for the key will be false.
     * @param ConfigScopeInterface $scope
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param string[] $emailAddresses
     * @return OrttoSubscriberInterface
     */
    public function getStateByEmailAddresses(ConfigScopeInterface $scope, bool $crossStore, array $emailAddresses);

    /**
     * @param ConfigScopeInterface $scope
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param string $email
     * @return bool
     */
    public function getStateByEmail(ConfigScopeInterface $scope, bool $crossStore, string $email);

    /**
     * Returns subscription statuss by customer ID.
     * @param ConfigScopeInterface $scope
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param int $customerId
     * @return bool
     */
    public function getStateByCustomerId(ConfigScopeInterface $scope, bool $crossStore, int $customerId);
}
