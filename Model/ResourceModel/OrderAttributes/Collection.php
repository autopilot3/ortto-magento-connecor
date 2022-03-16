<?php

namespace Autopilot\AP3Connector\Model\ResourceModel\OrderAttributes;

use Autopilot\AP3Connector\Api\Data\OrderAttributesInterface;
use Autopilot\AP3Connector\Api\SchemaInterface;
use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Model\OrderAttributes as Model;
use Autopilot\AP3Connector\Model\ResourceModel\OrderAttributes as ResourceModel;
use DateTime;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'autopilot_order_attributes_collection';

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
        $table = $this->getTable(SchemaInterface::TABLE_ORDER_ATTRIBUTES);
        $data = [
            OrderAttributesInterface::ORDER_ID => $orderId,
            OrderAttributesInterface::CANCELED_AT => $dateTime->format(Config::DB_DATE_TIME_FORMAT),
        ];
        $this->getConnection()->insertOnDuplicate($table, $data, [OrderAttributesInterface::CANCELED_AT]);
    }
}
