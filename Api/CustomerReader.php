<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

use Autopilot\AP3Connector\Model\Data\ReadCustomerResult;
use DateTime;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class CustomerReader implements CustomerReaderInterface
{
    private SearchCriteriaBuilder $criteriaBuilder;
    private CustomerRepositoryInterface $customerRepository;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->criteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getScopeCustomers(ConfigScopeInterface $scope, int $page, ?DateTime $updatedAfter = null)
    {
        if ($page < 1) {
            $page = 1;
        }
        $this->criteriaBuilder
            ->setPageSize(CustomerReaderInterface::PAGE_SIZE)
            ->setCurrentPage($page);

        if ($scope->getType() === ScopeInterface::SCOPE_WEBSITE) {
            $this->criteriaBuilder->addFilter(CustomerInterface::WEBSITE_ID, $scope->getId());
        } else {
            $this->criteriaBuilder->addFilter(CustomerInterface::STORE_ID, $scope->getId());
            $this->criteriaBuilder->addFilter(CustomerInterface::WEBSITE_ID, $scope->getWebsiteId());
        }

        if (!empty($updatedAfter)) {
            $this->criteriaBuilder->addFilter(CustomerInterface::UPDATED_AT, $updatedAfter, "gt");
        }

        $result = $this->customerRepository->getList($this->criteriaBuilder->create());
        return new ReadCustomerResult($result);
    }
}
