<?php


namespace Autopilot\AP3Connector\Observer\Customer;

use Autopilot\AP3Connector\Helper\HTTPClient;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProcessAfter implements ObserverInterface
{

    private HTTPClient $autopilotClient;

    public function __construct(HTTPClient $autopilotClient)
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
