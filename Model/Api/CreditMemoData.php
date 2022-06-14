<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Api;

use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;

class CreditMemoData
{
    private CreditmemoRepositoryInterface $repository;
    private SearchCriteriaBuilder $searchCriteria;
    private Data $helper;

    /** @var CreditmemoInterface[] $memos */
    private array $memos;

    public function __construct(
        CreditmemoRepositoryInterface $repository,
        SearchCriteriaBuilder $searchCriteria,
        Data $helper
    ) {
        $this->repository = $repository;
        $this->searchCriteria = $searchCriteria;
        $this->helper = $helper;
    }

    /**
     * @param int $orderId
     * @return bool
     */
    public function loadByOrderId(int $orderId): bool
    {
        $searchCriteria = $this->searchCriteria->addFilter('order_id', $orderId)->create();
        $this->memos = $this->repository->getList($searchCriteria)->getItems();
        if (empty($this->memos)) {
            return false;
        }
        return true;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->memos as $memo) {
            $result[] = [
                'id' => To::int($memo->getEntityId()),
                'invoice_id' => To::int($memo->getInvoiceId()),
                'number' => (string)$memo->getIncrementId(),
                'subtotal' => To::float($memo->getSubtotal()),
                'base_subtotal' => To::float($memo->getBaseSubtotal()),
                'subtotal_incl_tax' => To::float($memo->getSubtotalInclTax()),
                'base_subtotal_incl_tax' => To::float($memo->getBaseSubtotalInclTax()),
                'tax' => To::float($memo->getTaxAmount()),
                'base_tax' => To::float($memo->getBaseTaxAmount()),
                'shipping' => To::float($memo->getShippingAmount()),
                'base_shipping' => To::float($memo->getBaseShippingAmount()),
                'shipping_incl_tax' => To::float($memo->getShippingInclTax()),
                'base_shipping_incl_tax' => To::float($memo->getBaseShippingInclTax()),
                'grand_total' => To::float($memo->getGrandTotal()),
                'base_grand_total' => To::float($memo->getBaseGrandTotal()),
                'adjustment' => To::float($memo->getAdjustment()),
                'base_adjustment' => To::float($memo->getBaseAdjustment()),
                'items' => $this->getItemsArray($memo->getItems()),
                'refunded_at' => $this->helper->toUTC($memo->getCreatedAt()),
            ];
        }

        return $result;
    }

    /**
     * @param CreditmemoItemInterface[] $items
     * @return array
     */
    private function getItemsArray(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        $result = [];

        foreach ($items as $item) {
            $result[] = [
                'name' => (string)$item->getName(),
                'sku' => (string)$item->getSku(),
                'product_id' => To::int($item->getProductId()),
                'price' => To::float($item->getPrice()),
                'base_price' => To::float($item->getBasePrice()),
                'price_incl_tax' => To::float($item->getPriceInclTax()),
                'base_price_incl_tax' => To::float($item->getBasePriceInclTax()),
                'quantity' => To::float($item->getQty()),
                'tax' => To::float($item->getTaxAmount()),
                'base_tax' => To::float($item->getBaseTaxAmount()),
                'total' => To::float($item->getRowTotal()),
                'base_total' => To::float($item->getBaseRowTotal()),
                'base_total_incl_tax' => To::float($item->getBaseRowTotalInclTax()),
            ];
        }
        return $result;
    }
}
