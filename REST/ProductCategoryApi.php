<?php
declare(strict_types=1);


namespace Ortto\Connector\REST;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Exception;
use Ortto\Connector\Api\OrttoProductCategoryRepositoryInterface;
use Ortto\Connector\Api\ProductCategoryApiInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Logger\OrttoLoggerInterface;

class ProductCategoryApi extends RestApiBase implements ProductCategoryApiInterface
{
    private OrttoLoggerInterface $logger;
    private OrttoProductCategoryRepositoryInterface $repository;

    public function __construct(
        ScopeManagerInterface $scopeManager,
        OrttoLoggerInterface $logger,
        OrttoProductCategoryRepositoryInterface $repository
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
            $pageSize,
            []
        );
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function getById(string $scopeType, int $scopeId, int $categoryId)
    {
        try {
            $scope = $this->validateScope($scopeType, $scopeId);
            $category = $this->repository->getById($scope, $categoryId);
        } catch (NoSuchEntityException $e) {
            throw $this->notFoundError();
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw $this->httpError($e->getMessage());
        }
        if (empty($category)) {
            throw $this->notFoundError();
        }
        return $category;
    }
}
