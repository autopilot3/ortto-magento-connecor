<?php

namespace Ortto\Connector\Model\ResourceModel\OrderAttributes;

use Ortto\Connector\Api\Data\OrderAttributesInterface;
use Ortto\Connector\Api\SchemaInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Model\OrderAttributes as Model;
use Ortto\Connector\Model\ResourceModel\OrderAttributes as ResourceModel;
use DateTime;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'ortto_order_attributes_collection';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    /**
     * @param int $orderId
     * @return OrderAttributesInterface|bool
     */
    public function getByOrderId(int $orderId)
    {
        $result = $this->addFieldToSelect('*')
            ->addFieldToFilter(OrderAttributesInterface::ORDER_ID, $orderId)
            ->setPageSize(1);

        if ($result->getSize()) {
            return $result->getFirstItem();
        }

        return false;
    }

    public function setCancellationDate(int $orderId, DateTime $dateTime)
    {
        $this->insertDateIfNotSet($orderId, $dateTime, OrderAttributesInterface::CANCELED_AT);
    }

    public function setCompletionDate(int $orderId, DateTime $dateTime)
    {
        $this->insertDateIfNotSet($orderId, $dateTime, OrderAttributesInterface::COMPLETED_AT);
    }

    private function insertDateIfNotSet(int $orderId, DateTime $dateTime, string $dateField)
    {
        $table = $this->getTable(SchemaInterface::TABLE_ORDER_ATTRIBUTES);

        $connection = $this->getConnection();

        $condition = sprintf("%s = ?", OrderAttributesInterface::ORDER_ID);
        $select = $connection->select()->from($table)->where($condition, $orderId);
        $result = $connection->fetchAll($select);
        if (isset($result[$dateField])) {
            // We don't want to override the date if it's been set before
            return;
        }
        $data = [
            OrderAttributesInterface::ORDER_ID => $orderId,
            $dateField => $dateTime->format(Config::DB_DATE_TIME_FORMAT),
        ];
        $connection->insert($table, $data);
    }
}
