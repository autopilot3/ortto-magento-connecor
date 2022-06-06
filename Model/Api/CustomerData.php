<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Api;

use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\Subscriber;

class CustomerData
{
    public const SHIPPING_ADDRESS = "shipping_address";
    public const BILLING_ADDRESS = "billing_address";

    private CustomerInterface $customer;
    private string $group;
    private AddressInterface $billingAddress;
    private AddressInterface $shippingAddress;
    private string $phone;
    private array $customAttributes;

    private CustomerRepositoryInterface $customerRepository;
    private OrttoLogger $logger;
    private Subscriber $subscriber;
    private GroupRepositoryInterface $groupRepository;
    private Data $helper;
    private AddressDataFactory $addressDataFactory;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Subscriber $subscriber,
        GroupRepositoryInterface $groupRepository,
        OrttoLogger $logger,
        AddressDataFactory $addressDataFactory,
        Data $helper
    ) {
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->subscriber = $subscriber;
        $this->groupRepository = $groupRepository;
        $this->helper = $helper;
        $this->addressDataFactory = $addressDataFactory;
        $this->group = '';
        $this->phone = '';
        $this->customAttributes = [];
    }

    /**
     * @param int $id
     * @return bool
     */
    public function loadById(int $id)
    {
        try {
            $customer = $this->customerRepository->getById($id);
            $this->load($customer);
            return true;
        } catch (\Exception $e) {
            $this->logger->error($e, sprintf("Failed to load customer by ID %d", $id));
            return false;
        }
    }

    /**
     * @param CustomerInterface $customer
     * @return void
     */
    public function load($customer)
    {
        $this->customer = $customer;

        $groupId = $customer->getGroupId();
        if (!empty($groupId)) {
            try {
                $group = $this->groupRepository->getById($groupId);
                if (!empty($group)) {
                    $this->group = $group->getCode();
                }
            } catch (NoSuchEntityException|LocalizedException $e) {
                $this->logger->error($e, 'Failed to fetch customer group details');
            }
        }

        $addresses = $customer->getAddresses();

        if (!empty($addresses)) {
            $phoneSetToBilling = false;
            foreach ($addresses as $address) {
                switch (true) {
                    case $address->isDefaultBilling():
                        $this->billingAddress = $address;
                        $phone = $address->getTelephone();
                        if (!empty($phone)) {
                            $this->phone = $phone;
                            $phoneSetToBilling = true;
                        }
                        break;
                    case $address->isDefaultShipping():
                        $this->shippingAddress = $address;
                        // Billing phone number takes precedence
                        if (!$phoneSetToBilling) {
                            $phone = $address->getTelephone();
                            if (!empty($phone)) {
                                $this->phone = $phone;
                            }
                        }
                        break;
                    default:
                        if (empty($this->phone)) {
                            $this->phone = $address->getTelephone();
                        }
                }
            }
        }

        $attributes = $this->customer->getCustomAttributes();
        if (!empty($attributes)) {
            foreach ($attributes as $attr) {
                $this->customAttributes[$attr->getAttributeCode()] = $attr->getValue();
            }
        }
    }

    public function toArray(): array
    {
        $customerId = To::int($this->customer->getId());
        $sub = $this->subscriber->loadByCustomer($customerId, To::int($this->customer->getWebsiteId()));
        $data = [
            'id' => $customerId,
            'prefix' => (string)$this->customer->getPrefix(),
            'first_name' => (string)$this->customer->getFirstname(),
            'middle_name' => (string)$this->customer->getMiddlename(),
            'last_name' => (string)$this->customer->getLastname(),
            'suffix' => (string)$this->customer->getSuffix(),
            'email' => (string)$this->customer->getEmail(),
            'created_at' => $this->helper->toUTC($this->customer->getCreatedAt()),
            'updated_at' => $this->helper->toUTC($this->customer->getUpdatedAt()),
            'created_in' => (string)$this->customer->getCreatedIn(),
            'dob' => $this->helper->toUTC($this->customer->getDob()),
            'gender' => $this->helper->getGenderLabel($this->customer->getGender()),
            'group' => $this->group,
            'is_subscribed' => $sub->isSubscribed(),
            'phone' => $this->phone,
            'custom_attributes' => $this->customAttributes,
        ];

        if (!empty($this->billingAddress)) {
            $data[self::BILLING_ADDRESS] = $this->addressDataFactory->create()->toArray($this->billingAddress);
        }

        if (!empty($this->shippingAddress)) {
            $data[self::SHIPPING_ADDRESS] = $this->addressDataFactory->create()->toArray($this->shippingAddress);
        }

        return $data;
    }

    public function getEmail(): string
    {
        return $this->customer->getEmail();
    }

    public function getPhone(): string
    {
        return $this->phone;
    }
}
