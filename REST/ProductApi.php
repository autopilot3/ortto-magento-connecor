<?php
declare(strict_types=1);


namespace Ortto\Connector\REST;

use Magento\Framework\Webapi\Exception;
use Ortto\Connector\Api\ProductApiInterface;
use Ortto\Connector\Api\OrttoProductRepositoryInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Logger\OrttoLoggerInterface;

class ProductApi extends RestApiBase implements ProductApiInterface
{
    private OrttoLoggerInterface $logger;
    private OrttoProductRepositoryInterface $productRepository;

    public function __construct(
        ScopeManagerInterface $scopeManager,
        OrttoLoggerInterface $logger,
        OrttoProductRepositoryInterface $repository
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
