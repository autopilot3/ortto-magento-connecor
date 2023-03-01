<?php
declare(strict_types=1);


namespace Ortto\Connector\REST;

use Magento\Framework\Exception\NoSuchEntityException;
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

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function getById(string $scopeType, int $scopeId, int $productId)
    {
        try {
            $scope = $this->validateScope($scopeType, $scopeId);
            $product = $this->productRepository->getById($scope, $productId);
        } catch (NoSuchEntityException) {
            throw $this->notFoundError();
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw $this->httpError($e->getMessage());
        }
        if (empty($product)) {
            throw $this->notFoundError();
        }
        return $product;
    }
}
