<?php
declare(strict_types=1);


namespace Ortto\Connector\REST;

use Magento\Framework\Webapi\Exception;
use Ortto\Connector\Api\Data\OrttoSubscriberInterface;
use Ortto\Connector\Api\SubscriberApiInterface;
use Ortto\Connector\Api\OrttoSubscriberRepositoryInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Logger\OrttoLoggerInterface;

class SubscriberApi extends RestApiBase implements SubscriberApiInterface
{
    private OrttoLoggerInterface $logger;
    private OrttoSubscriberRepositoryInterface $repository;

    public function __construct(
        ScopeManagerInterface $scopeManager,
        OrttoLoggerInterface $logger,
        OrttoSubscriberRepositoryInterface $repository
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
        bool $crossStore,
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
            $crossStore
        );
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function getAll(
        int $storeId = -1,
        int $customerId = -1,
        string $email = '',
        int $state = -1,
        int $page = 1,
        int $pageSize = 100
    ) {
        try {
            $data = [];
            if ($storeId > -1) {
                $data[OrttoSubscriberInterface::STORE_ID] = $storeId;
            }
            if ($customerId > -1) {
                $data[OrttoSubscriberInterface::CUSTOMER_ID] = $customerId;
            }
            if (!empty($email)) {
                $data[OrttoSubscriberInterface::SUBSCRIBER_EMAIL] = $email;
            }
            if ($state > -1) {
                $data[OrttoSubscriberInterface::SUBSCRIBER_STATUS] = $state;
            }
            return $this->repository->getAll($page, $pageSize, $data);
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw $this->httpError($e->getMessage());
        }
    }


    /**
     * @inheritdoc
     * @throws Exception
     */
    public function getStateByEmail(string $scopeType, int $scopeId, bool $crossStore, string $email)
    {
        try {
            $scope = $this->validateScope($scopeType, $scopeId, false);
            return $this->repository->getStateByEmail($scope, $crossStore, $email);
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw $this->httpError($e->getMessage());
        }
    }
}
