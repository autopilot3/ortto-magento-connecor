<?php


namespace Autopilot\AP3Connector\Model\Data;

use Autopilot\AP3Connector\Api\Data\ReadCustomerResultInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;
use Magento\Framework\DataObject;

class ReadCustomerResult extends DataObject implements ReadCustomerResultInterface
{
    private int $total;
    private int $currentPage;
    /**
     * @var CustomerInterface[]
     */
    private array $customers;
    private int $pageSize;

    /**
     * @param CustomerSearchResultsInterface $result
     * @param array $data
     */
    public function __construct(
        CustomerSearchResultsInterface $result,
        array $data = []
    ) {
        parent::__construct($data);
        $this->total = $result->getTotalCount();
        $this->customers = $result->getItems();
        $searchCriteria = $result->getSearchCriteria();
        if ($searchCriteria !== null) {
            $this->currentPage = $searchCriteria->getCurrentPage();
            $this->pageSize = $searchCriteria->getPageSize();
        }
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getCustomers(): array
    {
        return $this->customers;
    }

    public function hasMore(): bool
    {
        return count($this->customers) >= $this->pageSize;
    }
}
