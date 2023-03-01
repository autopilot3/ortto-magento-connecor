<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface OrttoProductCategoryRepositoryInterface
{
    /**
     * @param ConfigScopeInterface $scope
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListProductCategoryResponseInterface
     */
    public function getList(
        ConfigScopeInterface $scope,
        int $page,
        string $checkpoint,
        int $pageSize,
        array $data = []
    );

    /**
     * Returns a product category by ID.
     * @param int $categoryId
     * @param array $data
     * @return \Ortto\Connector\Api\Data\OrttoProductCategoryInterface
     */
    public function getById(ConfigScopeInterface $scope, int $categoryId);
}
