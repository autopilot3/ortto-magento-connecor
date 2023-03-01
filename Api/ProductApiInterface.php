<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface ProductApiInterface
{
    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @return \Ortto\Connector\Api\Data\ListProductResponseInterface
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
     * @param int $productId
     * @return \Ortto\Connector\Api\Data\OrttoProductInterface
     */
    public function getById(
        string $scopeType,
        int $scopeId,
        int $productId
    );
}
