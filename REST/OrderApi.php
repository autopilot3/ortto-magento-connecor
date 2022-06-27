<?php
declare(strict_types=1);


namespace Ortto\Connector\REST;

use Magento\Framework\Webapi\Exception;
use Ortto\Connector\Api\OrderApiInterface;
use Ortto\Connector\Api\OrttoOrderRepositoryInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Logger\OrttoLoggerInterface;

class OrderApi extends RestApiBase implements OrderApiInterface
{
    private OrttoLoggerInterface $logger;
    private OrttoOrderRepositoryInterface $orderRepository;

    public function __construct(
        ScopeManagerInterface $scopeManager,
        OrttoLoggerInterface $logger,
        OrttoOrderRepositoryInterface $repository
    ) {
        parent::__construct($scopeManager);
        $this->logger = $logger;
        $this->orderRepository = $repository;
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
        return $this->orderRepository->getList(
            $scope,
            $page,
            $checkpoint,
            $pageSize
        );
    }
}
