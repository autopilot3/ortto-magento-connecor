<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Model\ResourceModel\CustomerAttributes;

use Autopilot\AP3Connector\Api\Data\CustomerAttributesInterface;
use Autopilot\AP3Connector\Model\CustomerAttributes as Model;
use AutoPilot\AP3Connector\Model\CustomerAttributesFactory as CustomerAttrFactory;
use Exception;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Autopilot\AP3Connector\Model\ResourceModel\CustomerAttributes as ResourceModel;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    private CustomerAttrFactory $attrFactory;

    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        CustomerAttrFactory $attrFactory,
        ManagerInterface $eventManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->attrFactory = $attrFactory;
    }

    /**
     * @param int $customerId
     * @return CustomerAttributesInterface|bool
     */
    public function getByCustomerId(int $customerId)
    {
        $result = $this->addFieldToSelect('*')
            ->addFieldToFilter(CustomerAttributesInterface::CUSTOMER_ID, $customerId)
            ->setPageSize(1);

        if ($result->getSize()) {
            return $result->getFirstItem();
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public function saveAutopilotContact(int $customerId, string $contactId)
    {
        $attr = $this->getByCustomerId($customerId);
        if (empty($attr)) {
            $attr = $this->attrFactory->create();
            $attr->setAutopilotContactId($contactId);
            $attr->setCustomerId($customerId);
            $this->addItem($attr);
        } else {
            $attr->setAutopilotContactId($contactId);
        }
        $this->save();
    }

    /**
     * @return CustomerAttributesInterface[]
     */
    public function getAll(int $page = 1, int $pageSize = 100): array
    {
        return $this->addFieldToSelect('*')
            ->setPageSize($pageSize)
            ->setCurPage($page)
            ->getItems();
    }
}
