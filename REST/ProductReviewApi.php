<?php
declare(strict_types=1);


namespace Ortto\Connector\REST;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Exception;
use Ortto\Connector\Api\ProductReviewApiInterface;
use Ortto\Connector\Api\OrttoProductReviewRepositoryInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Logger\OrttoLoggerInterface;

class ProductReviewApi extends RestApiBase implements ProductReviewApiInterface
{
    private OrttoLoggerInterface $logger;
    private OrttoProductReviewRepositoryInterface $repository;

    public function __construct(
        ScopeManagerInterface $scopeManager,
        OrttoLoggerInterface $logger,
        OrttoProductReviewRepositoryInterface $repository
    ) {
        parent::__construct($scopeManager);
        $this->logger = $logger;
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function list(
        string $scopeType,
        int $scopeId,
        int $page = 1,
        string $checkpoint = '',
        int $pageSize = 100
    ) {
        $scope = $this->validateScope($scopeType, $scopeId);
        return $this->repository->getList(
            $scope,
            $page,
            $checkpoint,
            $pageSize
        );
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function getById(string $scopeType, int $scopeId, int $reviewId)
    {
        try {
            $scope = $this->validateScope($scopeType, $scopeId);
            $review = $this->repository->getById($scope, $reviewId);
        } catch (NoSuchEntityException $e) {
            throw $this->notFoundError();
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw $this->httpError($e->getMessage());
        }
        if (empty($review)) {
            throw $this->notFoundError();
        }
        return $review;
    }
}
