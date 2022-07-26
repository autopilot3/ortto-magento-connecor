<?php
declare(strict_types=1);


namespace Ortto\Connector\REST;

use Magento\Framework\Webapi\Exception;
use Ortto\Connector\Api\ProductReviewApiInterface;
use Ortto\Connector\Api\OrttoProductReviewRepositoryInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Logger\OrttoLoggerInterface;

class ProductReviewApi extends RestApiBase implements ProductReviewApiInterface
{
    private OrttoLoggerInterface $logger;
    private OrttoProductReviewRepositoryInterface $productRepository;

    public function __construct(
        ScopeManagerInterface $scopeManager,
        OrttoLoggerInterface $logger,
        OrttoProductReviewRepositoryInterface $repository
    ) {
        parent::__construct($scopeManager);
        $this->logger = $logger;
        $this->productRepository = $repository;
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
        return $this->productRepository->getList(
            $scope,
            $page,
            $checkpoint,
            $pageSize
        );
    }
}
