<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface ProductReviewApiInterface
{
    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @return \Ortto\Connector\Api\Data\ListProductReviewResponseInterface
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
     * @param int $reviewId
     * @return \Ortto\Connector\Api\Data\OrttoProductReviewInterface
     */
    public function getById(
        string $scopeType,
        int $scopeId,
        int $reviewId
    );
}
