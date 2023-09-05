<?php
declare(strict_types=1);


namespace Ortto\Connector\Plugin;


use Closure;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;

class CustomerSessionContext
{
    protected Session $customerSession;
    protected Context $httpContext;
    private OrttoLogger $logger;

    /**
     * @param Session $customerSession
     * @param Context $httpContext
     * @param OrttoLogger $logger
     */
    public function __construct(
        Session $customerSession,
        Context $httpContext,
        OrttoLogger $logger
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->logger = $logger;
    }

    /**
     * @param ActionInterface $subject
     * @param Closure $proceed
     * @param RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(
        ActionInterface $subject,
        Closure $proceed,
        RequestInterface $request
    ) {
        /*
            We can’t get any data from customer session when a cache is enabled Because as soon as layout generation started.
            Customer session will be cleared by \Magento\PageCache\Model\Layout\DepersonalizePlugin::afterGenerateXml on all cacheable pages.
            So we can’t get any customer session data from \Magento\Customer\Model\Session.
         */
        try {
            $isLoggedIn = To::bool($this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH));
            if ($isLoggedIn) {
                $this->httpContext->setValue(
                    TrackDataProviderInterface::CUSTOMER_ID_SESSION_KEY,
                    $this->customerSession->getCustomerId(),
                    false
                );

                $customer = $this->customerSession->getCustomer();

                $this->httpContext->setValue(
                    TrackDataProviderInterface::CUSTOMER_EMAIL_SESSION_KEY,
                    $customer->getEmail(),
                    ''
                );

                $this->httpContext->setValue(
                    TrackDataProviderInterface::CUSTOMER_PHONE_SESSION_KEY,
                    $this->getCustomerPhoneNumber($customer),
                    ''
                );
            }

        } catch (\Exception $e) {
            $this->logger->error($e, "Failed to set customer session data");
        }

        return $proceed($request);
    }

    /**
     * @param Customer $customer
     * @return string
     */
    private function getCustomerPhoneNumber(Customer $customer): string
    {
        $phone = '';
        try {
            $addresses = $customer->getAddresses();
            if (empty($addresses)) {
                return '';
            }

            $shipping = $customer->getDefaultShippingAddress();
            $billing = $customer->getDefaultBillingAddress();


            foreach ($addresses as $address) {
                if ($address instanceof Address) {
                    switch (true) {
                        case $billing && $billing->getEntityId() == $address->getEntityId():
                            $billingPhone = $address->getTelephone();
                            if (!empty($billingPhone)) {
                                // Billing phone number takes precedence
                                return $billingPhone;
                            }
                            break;
                        case $shipping && $shipping->getEntityId() == $address->getEntityId():
                            $shippingPhone = $address->getTelephone();
                            if (!empty($shippingPhone)) {
                                // Shipping phone overrides other addresses' phone number (except billing)
                                $phone = $shippingPhone;
                            }
                            break;
                        default:
                            if (empty($phone)) {
                                $phone = $address->getTelephone();
                            }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e, "Failed to set tracking phone number");
        }

        return $phone;
    }
}
