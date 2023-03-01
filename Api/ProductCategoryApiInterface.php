<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface ProductCategoryApiInterface
{
    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @return \Ortto\Connector\Api\Data\ListProductCategoryResponseInterface
     */
    public function list(
        string $scopeType,
        int $scopeId,
        int $page = 1,
        string $checkpoint = '',
        int $pageSize = 100
    );

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param int $categoryId
     * @return \Ortto\Connector\Api\Data\OrttoProductCategoryInterface
     */
    public function getById(
        string $scopeType,
        int $scopeId,
        int $categoryId
    );
}
