<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\OrttoProductReviewInterface;

interface OrttoProductReviewRepositoryInterface
{
    /**
     * @param ConfigScopeInterface $scope
     * @param bool $newsletter Enables checking newsletter subscription status
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListProductReviewResponseInterface
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
     * @param ConfigScopeInterface $scope
     * @param bool $newsletter Enables checking newsletter subscription status
     * @param bool $crossStore Enables checking newsletter subscription status across all stores
     * @param int $reviewId
     * @param array $data
     * @return OrttoProductReviewInterface
     */
    public function getById(
        ConfigScopeInterface $scope,
        bool $newsletter,
        bool $crossStore,
        int $reviewId,
        array $data = []
    );
}
