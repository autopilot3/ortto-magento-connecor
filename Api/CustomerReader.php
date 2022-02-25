<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Model\Data\CustomerData;
use Autopilot\AP3Connector\Model\Data\ReadCustomerResult;
use DateTime;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class CustomerReader implements CustomerReaderInterface
{
    private SearchCriteriaBuilder $customerCriteriaBuilder;
    private CustomerRepositoryInterface $customerRepository;
    private SearchCriteriaBuilder $orderCriteriaBuilder;
    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $orderCriteriaBuilder,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->customerCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepository = $customerRepository;
        $this->orderCriteriaBuilder = $orderCriteriaBuilder;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getScopeCustomers(
        ConfigScopeInterface $scope,
        int $page,
        ?DateTime $customerCheckpoint = null,
        ?DateTime $orderCheckpoint = null
    ) {
        if ($page < 1) {
            $page = 1;
        }
        $this->customerCriteriaBuilder
            ->setPageSize(CustomerReaderInterface::PAGE_SIZE)
            ->setCurrentPage($page);

        if ($scope->getType() === ScopeInterface::SCOPE_WEBSITE) {
            $this->customerCriteriaBuilder->addFilter(CustomerInterface::WEBSITE_ID, $scope->getId());
        } else {
            $this->customerCriteriaBuilder->addFilter(CustomerInterface::STORE_ID, $scope->getId());
            $this->customerCriteriaBuilder->addFilter(CustomerInterface::WEBSITE_ID, $scope->getWebsiteId());
        }

        if (!empty($customerCheckpoint)) {
            $this->customerCriteriaBuilder->addFilter(CustomerInterface::UPDATED_AT, $customerCheckpoint, "gt");
        }

        $customerData = [];
        $result = $this->customerRepository->getList($this->customerCriteriaBuilder->create());
        $customers = $result->getItems();
        if (!empty($customers)) {
            foreach ($customers as $customer) {
                $orders = $this->getOrders((int)$customer->getId(), $orderCheckpoint);
                $customerData[] = new CustomerData($customer, $orders);
            }
        }

        return new ReadCustomerResult($customerData, $page, $result->getTotalCount());
    }


    /**
     * @param int $customerId
     * @param DateTime|null $checkpoint
     * @return OrderInterface[]
     */
    private function getOrders(int $customerId, ?DateTime $checkpoint = null): array
    {
        $this->orderCriteriaBuilder
            ->setPageSize(CustomerReaderInterface::PAGE_SIZE)
            ->addFilter('customer_id', $customerId);

        if (!empty($checkpoint)) {
            $this->orderCriteriaBuilder->addFilter('updated_at', $checkpoint, "gt");
        }

        $page = 1;
        $allOrders = [];
        do {
            $this->orderCriteriaBuilder->setCurrentPage($page);
            $result = $this->orderRepository->getList($this->orderCriteriaBuilder->create());
            $page += 1;
            $orders = $result->getItems();
            if (!empty($orders)) {
                $allOrders[] = $orders;
            }
        } while (count($orders) >= CustomerReaderInterface::PAGE_SIZE);
        return $allOrders;
    }
}
