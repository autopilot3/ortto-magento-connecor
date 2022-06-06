<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\ResourceModel\CronCheckpoint;

use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\Data\CronCheckpointInterface;
use Ortto\Connector\Api\Data\CronCheckpointInterface as Checkpoint;
use Ortto\Connector\Api\SchemaInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Model\ResourceModel\CronCheckpoint as ResourceModel;
use Ortto\Connector\Model\CronCheckpointFactory;
use Ortto\Connector\Model\CronCheckpoint as Model;
use DateTime;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;
use Exception;

class Collection extends AbstractCollection
{
    protected $_eventPrefix = SchemaInterface::TABLE_CRON_CHECKPOINT . '_collection';
    protected $_eventObject = 'cron_checkpoint_collection';
    protected $_idFieldName = CronCheckpointInterface::ENTITY_ID;

    private CronCheckpointFactory $cronCheckpointFactory;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        CronCheckpointFactory $cronCheckpointFactory,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->cronCheckpointFactory = $cronCheckpointFactory;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    /**
     * @param string $category
     * @param ConfigScopeInterface $scope
     * @return CronCheckpointInterface|bool
     * @throws Exception
     */
    public function getCheckpoint(string $category, ConfigScopeInterface $scope)
    {
        $checkpoint = $this->get($category, $scope);
        if (!empty($checkpoint)) {
            return $checkpoint;
        }

        $checkpoint = $this->cronCheckpointFactory->create();
        $checkpoint->setCategory($category);
        $checkpoint->setScopeType($scope->getType());
        $checkpoint->setScopeId($scope->getId());

        return $checkpoint;
    }

    /**
     * @param string $category
     * @param DateTime $date
     * @param ConfigScopeInterface $scope
     * @throws Exception
     */
    public function setCheckpoint(string $category, DateTime $date, ConfigScopeInterface $scope)
    {
        $table = $this->getTable(SchemaInterface::TABLE_CRON_CHECKPOINT);
        $data = [
            Checkpoint::CATEGORY => $category,
            Checkpoint::SCOPE_TYPE => $scope->getType(),
            Checkpoint::SCOPE_ID => $scope->getId(),
            Checkpoint::LAST_CHECKED_AT => $date->format(Config::DB_DATE_TIME_FORMAT),
        ];
        $this->getConnection()->insertOnDuplicate($table, $data, [Checkpoint::LAST_CHECKED_AT]);
    }

    /**
     * @param string $category
     * @param ConfigScopeInterface $scope
     * @return CronCheckpointInterface|bool
     */
    private function get(string $category, ConfigScopeInterface $scope)
    {
        $result = $this->addFieldToSelect("*")
            ->addFieldToFilter(Checkpoint::CATEGORY, $category)
            ->addFieldToFilter(Checkpoint::SCOPE_TYPE, $scope->getType())
            ->addFieldToFilter(Checkpoint::SCOPE_ID, $scope->getId())
            ->setPageSize(1);

        if ($result->getSize()) {
            return $result->getFirstItem();
        }
        return false;
    }
}
