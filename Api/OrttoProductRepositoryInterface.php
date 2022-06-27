<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface OrttoProductRepositoryInterface
{
    /**
     * @param ConfigScopeInterface $scope
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListProductResponseInterface
     */
    public function getList(
        ConfigScopeInterface $scope,
        int $page,
        string $checkpoint,
        int $pageSize,
        array $data = []
    );

    /**
     * Returns the list of products by IDs. The returned array is keyed by product ID.
     * In case any product was not found, the value for the key will be null.
     * @param ConfigScopeInterface $scope
     * @param int[] $productIds
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListProductResponseInterface
     */
    public function getByIds(ConfigScopeInterface $scope, array $productIds, array $data = []);
}
