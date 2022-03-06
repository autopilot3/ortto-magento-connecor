<?php
declare(strict_types=1);


namespace Autopilot\AP3Connector\Plugin;

use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;

class LoadCustomerAttributesPlugin
{

    private CustomerExtensionFactory $extensionFactory;

    public function __construct(CustomerExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    public function afterGetExtensionAttributes(
        CustomerInterface $entity,
        CustomerExtensionInterface $extension = null
    ) {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }

        return $extension;
    }
}
