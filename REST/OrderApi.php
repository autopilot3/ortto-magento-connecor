<?php
declare(strict_types=1);


namespace Ortto\Connector\REST;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Exception;
use Ortto\Connector\Api\Data\OrttoOrderInterface;
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
        bool $newsletter,
        bool $crossStore,
        int $page = 1,
        string $checkpoint = '',
        int $pageSize = 100,
        int $customerId = -1,
        string $customerEmail = '',
        bool $anonymous = false
    ) {
        $scope = $this->validateScope($scopeType, $scopeId);
        $data = [];
        if ($anonymous) {
            $data[OrttoOrderInterface::ANONYMOUS_CUSTOMERS] = true;
        } else {
            if ($customerId > 0) {
                $data[OrttoOrderInterface::CUSTOMER_ID] = $customerId;
            }
        }
        if (!empty($customerEmail)) {
            $data[OrttoOrderInterface::CUSTOMER_EMAIL] = $customerEmail;
        }
        return $this->orderRepository->getList(
            $scope,
            $newsletter,
            $crossStore,
            $page,
            $checkpoint,
            $pageSize,
            $data
        );
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function getById(
        string $scopeType,
        int $scopeId,
        bool $newsletter,
        bool $crossStore,
        int $orderId
    ) {
        try {
            $scope = $this->validateScope($scopeType, $scopeId);
            $order = $this->orderRepository->getById($scope, $newsletter, $crossStore, $orderId);
        } catch (NoSuchEntityException $e) {
            throw $this->notFoundError();
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw $this->httpError($e->getMessage());
        }
        if (empty($order)) {
            throw $this->notFoundError();
        }
        return $order;
    }
}
