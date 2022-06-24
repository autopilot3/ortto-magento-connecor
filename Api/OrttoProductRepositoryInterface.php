<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface OrttoProductRepositoryInterface
{
    public const LOAD_CATEGORIES = "load_categories";

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
}
