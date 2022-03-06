<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Plugin;

use Autopilot\AP3Connector\Logger\Logger;
use AutoPilot\AP3Connector\Model\ResourceModel\CustomerAttributes\CollectionFactory as CustomerAttrCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;

class CustomerRepositoryPlugin
{
    private CustomerAttrCollectionFactory $attrCollectionFactory;
    private Logger $logger;

    public function __construct(
        CustomerAttrCollectionFactory $attrCollectionFactory,
        Logger $logger
    ) {
        $this->attrCollectionFactory = $attrCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    public function afterGet(CustomerRepositoryInterface $subject, CustomerInterface $customer): CustomerInterface
    {
        $extAttributes = $customer->getExtensionAttributes();
        if ($extAttributes && $extAttributes->getAutopilotContactId()) {
            return $customer;
        }

        $attributesCollection = $this->attrCollectionFactory->create();
        $attribute = $attributesCollection->getByCustomerId((int)$customer->getId());
        if (!empty($attribute)) {
            $extAttributes->setAutopilotContactId($attribute->getAutopilotContactId());
            $customer->setExtensionAttributes($extAttributes);
        }
        return $customer;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerSearchResultsInterface $customerSearchResults
     * @return CustomerSearchResultsInterface
     */
    public function afterGetList(
        CustomerRepositoryInterface $subject,
        CustomerSearchResultsInterface $customerSearchResults
    ): CustomerSearchResultsInterface {
        foreach ($customerSearchResults->getItems() as $customer) {
            $this->afterGet($subject, $customer);
        }
        return $customerSearchResults;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     */
    public function beforeSave(CustomerRepositoryInterface $subject, CustomerInterface $customer)
    {
        try {
            $extAttributes = $customer->getExtensionAttributes();
            $contactId = $extAttributes->getAutopilotContactId();
            if (!empty($contactId)) {
                $attributesCollection = $this->attrCollectionFactory->create();
                $attributesCollection->saveAutopilotContact((int)$customer->getId(), $contactId);
            }
        } catch (\Exception $e) {
            $this->logger->error($e, "Failed to store customer attributes");
        }
    }
}
