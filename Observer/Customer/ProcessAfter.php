<?php


namespace Autopilot\AP3Connector\Observer\Customer;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProcessAfter implements ObserverInterface
{
    private AutopilotClientInterface $autopilotClient;

    public function __construct(AutopilotClientInterface $autopilotClient)
    {
        $this->autopilotClient = $autopilotClient;
    }

    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $customer = $event->getData('customer');
        $this->autopilotClient->upsertContactBackend($customer);
    }
}
