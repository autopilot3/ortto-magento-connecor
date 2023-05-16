<?php
declare(strict_types=1);

namespace Ortto\Connector\REST;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Exception;
use Ortto\Connector\Api\CustomerApiInterface;
use Ortto\Connector\Api\OrttoCustomerRepositoryInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Logger\OrttoLoggerInterface;

class CustomerApi extends RestApiBase implements CustomerApiInterface
{
    private OrttoLoggerInterface $logger;
    private OrttoCustomerRepositoryInterface $customerRepository;

    public function __construct(
        ScopeManagerInterface $scopeManager,
        OrttoLoggerInterface $logger,
        OrttoCustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($scopeManager);
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
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
        bool $anonymous = false
    ) {
        $scope = $this->validateScope($scopeType, $scopeId);
        return $this->customerRepository->getList(
            $scope,
            $newsletter,
            $crossStore,
            $page,
            $checkpoint,
            $pageSize,
            [OrttoCustomerRepositoryInterface::ANONYMOUS => $anonymous]
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
        int $customerId
    ) {
        try {
            $scope = $this->validateScope($scopeType, $scopeId, false);
            $customer = $this->customerRepository->getById($scope, $newsletter, $crossStore, $customerId);
        } catch (NoSuchEntityException $e) {
            throw $this->notFoundError();
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw $this->httpError($e->getMessage());
        }
        if (empty($customer)) {
            throw $this->notFoundError();
        }
        return $customer;
    }


    /** @ingeritdoc
     * @throws Exception
     */
    public function getByEmail(
        string $scopeType,
        int $scopeId,
        bool $newsletter,
        bool $crossStore,
        string $email
    ) {
        try {
            $scope = $this->validateScope($scopeType, $scopeId, false);
            return $this->customerRepository->getByEmail($scope, $newsletter, $crossStore, $email);
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw $this->httpError($e->getMessage());
        }
    }
}
