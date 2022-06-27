<?php
declare(strict_types=1);


namespace Ortto\Connector\REST;

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
}
