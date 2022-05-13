<?php
declare(strict_types=1);

namespace Ortto\Connector\Observer;

use Ortto\Connector\Helper\Data;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Api\OrttoClientInterface;
use Ortto\Connector\Api\ScopeManagerInterface;
use Ortto\Connector\Api\ConfigScopeInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Event\Observer;
use Exception;

class CustomerSavedAfterFrontend implements ObserverInterface
{
    private OrttoLoggerInterface $logger;
    private ScopeManagerInterface $scopeManager;
    private OrttoClientInterface $orttoClient;
    private CustomerRepository $customerRepository;
    private Data $helper;

    public function __construct(
        OrttoLoggerInterface $logger,
        ScopeManagerInterface $scopeManager,
        OrttoClientInterface $orttoClient,
        CustomerRepository $customerRepository,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->scopeManager = $scopeManager;
        $this->orttoClient = $orttoClient;
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            /** @var string $email */
            $email = $event->getData(CustomerInterface::EMAIL);
            if (empty($email)) {
                $this->logger->warn("Customer email was not provided");
                return;
            }
            $customer = $this->customerRepository->get($email);
            $scopes = $this->scopeManager->getActiveScopes();
            foreach ($scopes as $scope) {
                if (!$this->helper->shouldExportCustomer($scope, $customer)) {
                    continue;
                }
                $this->orttoClient->importContacts($scope, [$customer]);
            }
        } catch (Exception $e) {
            $this->logger->error($e, 'CustomerSavedAfterFrontend: Failed to export the customer');
        }
    }
}
