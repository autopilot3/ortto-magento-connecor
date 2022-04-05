<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Controller\Product;

use Autopilot\AP3Connector\Api\AutopilotClientInterface;
use Autopilot\AP3Connector\Api\ConfigScopeInterface;
use Autopilot\AP3Connector\Api\ScopeManagerInterface;
use Autopilot\AP3Connector\Helper\Data;
use Autopilot\AP3Connector\Helper\To;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Autopilot\AP3Connector\Model\Api\ProductDataFactory;
use Exception;

class View implements HttpPostActionInterface
{
    private const PRODUCT_ID = 'product_id';
    private const CUSTOMER_ID = 'customer_id';
    private const STORE_ID = 'store_id';

    private AutopilotLoggerInterface $logger;
    private JsonFactory $jsonFactory;
    private Data $helper;
    private ScopeManagerInterface $scopeManager;
    private ProductRepositoryInterface $productRepository;
    private RequestInterface $request;
    private CustomerRepositoryInterface $customerRepository;
    private AutopilotClientInterface $autopilotClient;
    private ProductDataFactory $productDataFactory;

    public function __construct(
        AutopilotLoggerInterface $logger,
        JsonFactory $jsonFactory,
        Data $helper,
        ScopeManagerInterface $scopeManager,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        AutopilotClientInterface $autopilotClient,
        RequestInterface $request,
        ProductDataFactory $productDataFactory
    ) {
        $this->logger = $logger;
        $this->jsonFactory = $jsonFactory;
        $this->helper = $helper;
        $this->scopeManager = $scopeManager;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->customerRepository = $customerRepository;
        $this->autopilotClient = $autopilotClient;
        $this->productDataFactory = $productDataFactory;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $result = $this->jsonFactory->create();
        try {
            $storeId = To::int($this->request->getParam(self::STORE_ID));
            $activeScopes = $this->scopeManager->getActiveScopes();
            /**
             * @var $scopes ConfigScopeInterface[]
             */
            $scopes = [];
            foreach ($activeScopes as $scope) {
                $storeIds = $scope->getStoreIds();
                if (!array_contains($storeIds, $storeId)) {
                    continue;
                }
                $scopes[] = $scope;
            }
            if (empty($scopes)) {
                return $result;
            }

            $customerId = To::int($this->request->getParam(self::CUSTOMER_ID));
            $customer = $this->customerRepository->getById($customerId);
            $productId = To::int($this->request->getParam(self::PRODUCT_ID));
            $productData = $this->productDataFactory->create();
            $productData->load($this->productRepository->getById($productId));
            $product = $productData->toArray();
            foreach ($scopes as $scope) {
                $customerData = $this->helper->getCustomerFields($customer, $scope);
                if (empty($customerData)) {
                    continue;
                }
                $this->autopilotClient->ingestProductView($scope, $product, $customerData);
            }
        } catch (Exception $e) {
            $this->logger->error($e, "Failed to send product view event");
        }
        return $result;
    }
}
