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
        int $page = 1,
        string $checkpoint = '',
        int $pageSize = 100,
        bool $anonymous = false
    ) {
        $scope = $this->validateScope($scopeType, $scopeId);
        return $this->customerRepository->getList(
            $scope,
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
    public function getById(int $customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
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
}
