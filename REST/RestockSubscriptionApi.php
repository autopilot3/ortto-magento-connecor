<?php
declare(strict_types=1);


namespace Ortto\Connector\REST;

use Magento\Framework\Webapi\Exception;
use Ortto\Connector\Api\RestockSubscriptionApiInterface;
use Ortto\Connector\Api\OrttoRestockSubscriptionRepositoryInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Logger\OrttoLoggerInterface;

class RestockSubscriptionApi extends RestApiBase implements RestockSubscriptionApiInterface
{
    private OrttoLoggerInterface $logger;
    private OrttoRestockSubscriptionRepositoryInterface $repository;

    public function __construct(
        ScopeManagerInterface $scopeManager,
        OrttoLoggerInterface $logger,
        OrttoRestockSubscriptionRepositoryInterface $repository
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
        bool $newsletter,
        bool $crossStore,
        int $page = 1,
        string $checkpoint = '',
        int $pageSize = 100
    ) {
        $scope = $this->validateScope($scopeType, $scopeId);
        return $this->repository->getList(
            $scope,
            $newsletter,
            $crossStore,
            $page,
            $checkpoint,
            $pageSize
        );
    }
}
