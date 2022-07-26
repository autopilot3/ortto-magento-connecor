<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\OrttoProductReviewInterface;

interface OrttoProductReviewRepositoryInterface
{
    /**
     * @param ConfigScopeInterface $scope
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListProductReviewResponseInterface
     */
    public function getList(
        ConfigScopeInterface $scope,
        int $page,
        string $checkpoint,
        int $pageSize,
        array $data = []
    );

    /**
     * @param ConfigScopeInterface $scope
     * @param int $reviewId
     * @param array $data
     * @return OrttoProductReviewInterface
     */
    public function getById(ConfigScopeInterface $scope, int $reviewId, array $data = []);
}
