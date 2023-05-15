<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Api;

use Magento\Framework\Api\SortOrder;
use Magento\Newsletter\Model\Subscriber;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\Data\OrttoSubscriberInterface;
use Ortto\Connector\Api\OrttoSubscriberRepositoryInterface;
use Ortto\Connector\Helper\Config;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Ortto\Connector\Model\Data\OrttoSubscriberFactory;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as SubscriberCollectionFactory;
use Ortto\Connector\Model\Data\ListSubscriberResponseFactory;

class OrttoSubscriberRepository implements OrttoSubscriberRepositoryInterface
{
    private Data $helper;
    private OrttoLogger $logger;
    private OrttoSubscriberFactory $orttoSubscriberFactory;
    private SubscriberCollectionFactory $subscriberCollectionFactory;
    private ListSubscriberResponseFactory $listResponseFactory;

    public function __construct(
        Data $helper,
        OrttoLogger $logger,
        OrttoSubscriberFactory $orttoSubscriberFactory,
        SubscriberCollectionFactory $subscriberCollectionFactory,
        ListSubscriberResponseFactory $listResponseFactory
    ) {

        $this->helper = $helper;
        $this->logger = $logger;
        $this->orttoSubscriberFactory = $orttoSubscriberFactory;
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->listResponseFactory = $listResponseFactory;
    }

    /** @inheirtDoc */
    public function getAll(int $page, int $pageSize, array $data = [])
    {
        if ($page < 1) {
            $page = 1;
        }
        if ($pageSize == 0) {
            $pageSize = 100;
        }
        $collection = $this->subscriberCollectionFactory->create()
            ->setCurPage($page)
            ->addFieldToSelect('*')
            ->setOrder(OrttoSubscriberInterface::CUSTOMER_ID, SortOrder::SORT_ASC)
            ->setPageSize($pageSize);

        if (array_key_exists(OrttoSubscriberInterface::STORE_ID, $data)) {
            if ($storeId = $data[OrttoSubscriberInterface::STORE_ID]) {
                $collection->addStoreFilter(To::int($storeId));
            }
        }

        if (array_key_exists(OrttoSubscriberInterface::SUBSCRIBER_EMAIL, $data)) {
            if ($email = $data[OrttoSubscriberInterface::SUBSCRIBER_EMAIL]) {
                $collection->addFieldToFilter(OrttoSubscriberInterface::SUBSCRIBER_EMAIL, ['eq' => $email]);
            }
        }

        if (array_key_exists(OrttoSubscriberInterface::CUSTOMER_ID, $data)) {
            if ($customerId = $data[OrttoSubscriberInterface::CUSTOMER_ID]) {
                $collection->addFieldToFilter(OrttoSubscriberInterface::CUSTOMER_ID, ['eq' => $customerId]);
            }
        }

        if (array_key_exists(OrttoSubscriberInterface::SUBSCRIBER_STATUS, $data)) {
            if ($status = $data[OrttoSubscriberInterface::SUBSCRIBER_STATUS]) {
                $collection->addFieldToFilter(OrttoSubscriberInterface::SUBSCRIBER_STATUS, ['eq' => To::int($status)]);
            }
        }

        $result = $this->listResponseFactory->create();
        $total = To::int($collection->getSize());
        $result->setTotal($total);
        if ($total == 0) {
            return $result;
        }

        $subscribers = [];
        /** @var Subscriber $subscriber */
        foreach ($collection->getItems() as $subscriber) {
            $subscribers[] = $this->convert($subscriber);
        }

        $result->setItems($subscribers);
        $result->setHasMore($page < $total / $pageSize);
        return $result;
    }

    /** @inheirtDoc */
    public function getList(
        ConfigScopeInterface $scope,
        int $page,
        string $checkpoint,
        int $pageSize,
        bool $crossStore,
        array $data = []
    ) {
        if ($page < 1) {
            $page = 1;
        }
        if ($pageSize == 0) {
            $pageSize = 100;
        }

        $collection = $this->subscriberCollectionFactory->create()
            ->setCurPage($page)
            ->addFieldToSelect('*')
            ->setOrder(OrttoSubscriberInterface::STORE_ID, SortOrder::SORT_ASC)
            ->setPageSize($pageSize);


        if (!$crossStore) {
            $collection->addStoreFilter($scope->getId());
        }

        if (!empty($checkpoint)) {
            $collection->addFieldToFilter(OrttoSubscriberInterface::CHANGE_STATUS_AT,
                ['gteq' => To::sqlDate($checkpoint)]);
        }

        $result = $this->listResponseFactory->create();
        $total = To::int($collection->getSize());
        $result->setTotal($total);
        if ($total == 0) {
            return $result;
        }

        $subscribers = [];
        /** @var Subscriber $subscriber */
        foreach ($collection->getItems() as $subscriber) {
            $subscribers[] = $this->convert($subscriber);
        }

        $result->setItems($subscribers);
        $result->setHasMore($page < $total / $pageSize);
        return $result;
    }

    /** @inheirtDoc */
    public function getStateByEmailAddresses(ConfigScopeInterface $scope, bool $crossStore, array $emailAddresses)
    {
        /** @var bool[] $subscribers */
        $subscribers = [];
        if (empty($emailAddresses)) {
            return $subscribers;
        }

        $emailAddresses = array_unique($emailAddresses, SORT_STRING);

        $collection = $this->subscriberCollectionFactory->create()
            ->addFieldToSelect('*')
            ->setOrder(OrttoSubscriberInterface::SUBSCRIBER_ID, SortOrder::SORT_ASC)
            ->addFieldToFilter(OrttoSubscriberInterface::SUBSCRIBER_STATUS, ['eq' => Subscriber::STATUS_SUBSCRIBED])
            ->addFieldToFilter(OrttoSubscriberInterface::SUBSCRIBER_EMAIL, ['in' => $emailAddresses]);


        if (!$crossStore) {
            $collection->addStoreFilter($scope->getId());
        }

        foreach ($emailAddresses as $email) {
            // The make sure all the keys always exist in the result array, even if the requested
            $subscribers[$email] = Config::DEFAULT_SUBSCRIPTION_STATUS;
        }

        /** @var Subscriber $subscriber */
        foreach ($collection->getItems() as $subscriber) {
            $email = (string)$subscriber->getEmail();
            $subscribers[$email] = To::int($subscriber->getStatus()) == Subscriber::STATUS_SUBSCRIBED;
        }

        return $subscribers;
    }

    /** @inheirtDoc */
    public function getStateByEmail(ConfigScopeInterface $scope, bool $crossStore, string $email)
    {
        $result = $this->getStateByEmailAddresses($scope, $crossStore, [$email]);
        return $result[$email];
    }

    /**
     * @param Subscriber $subscriber
     * @return OrttoSubscriberInterface
     */
    private function convert(Subscriber $subscriber)
    {
        $orttoSubscriber = $this->orttoSubscriberFactory->create();
        $orttoSubscriber->setId(To::int($subscriber->getId()));
        $orttoSubscriber->setStoreId(To::int($subscriber->getStoreId()));
        $orttoSubscriber->setEmail((string)$subscriber->getEmail());
        $orttoSubscriber->setStatusCode(To::int($subscriber->getStatus()));
        $orttoSubscriber->setUpdatedAt($this->helper->toUTC($subscriber->getChangeStatusAt()));
        $orttoSubscriber->setCustomerId(To::int($subscriber->getCustomerId()));
        return $orttoSubscriber;
    }
}
