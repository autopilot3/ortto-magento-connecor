<?php
declare(strict_types=1);


namespace Ortto\Connector\Block;

use Ortto\Connector\Api\ConfigurationReaderInterface;
use Ortto\Connector\Api\Data\TrackingOptionsInterface;
use Ortto\Connector\Api\TrackDataProviderInterface;
use Ortto\Connector\Logger\OrttoLogger;
use Magento\Framework\View\Element\Template;
use Ortto\Connector\Model\Data\TrackingOptionsFactory;

class HeaderJs extends Template
{
    private ConfigurationReaderInterface $configReader;
    private OrttoLogger $logger;
    private TrackDataProviderInterface $trackDataProvider;
    private TrackingOptionsFactory $optionsFactory;

    public function __construct(
        Template\Context $context,
        ConfigurationReaderInterface $configReader,
        OrttoLogger $logger,
        TrackDataProviderInterface $trackDataProvider,
        TrackingOptionsFactory $optionsFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configReader = $configReader;
        $this->logger = $logger;
        $this->trackDataProvider = $trackDataProvider;
        $this->optionsFactory = $optionsFactory;
    }

    /**
     * @return TrackingOptionsInterface|bool
     */
    public function getConfiguration()
    {

        try {
            $trackingData = $this->trackDataProvider->getData();
            if (!$trackingData->isTrackingEnabled()) {
                return false;
            }

            $scope = $trackingData->getScope();
            $options = $this->optionsFactory->create();
            $options->setScope($scope);
            $scopeType = $scope->getType();
            $storeId = $scope->getId();

            $captureJS = $this->configReader->getCaptureJsURL($scopeType, $storeId);
            if (empty($captureJS)) {
                throw new \Exception("Capture JS was empty");
            }
            $options->setCaptureJS($captureJS);

            $magentoJS = $this->configReader->getMagentoCaptureJsURL($scopeType, $storeId);
            if (empty($magentoJS)) {
                throw new \Exception("Magento JS was empty");
            }
            $options->setMagentoJS($magentoJS);

            $captureAPI = $this->configReader->getCaptureApiURL($scopeType, $storeId);
            if (empty($captureAPI)) {
                throw new \Exception("Capture API was empty");
            }

            $options->setCaptureAPI($captureAPI);

            $code = $this->configReader->getTrackingCode($scopeType, $storeId);
            if (empty($code)) {
                throw new \Exception("Tracking code was empty");
            }
            $options->setTrackingCode($code);

            $consentRequired = $this->configReader->isConsentToTrackRequired($scopeType, $storeId);
            $options->setNeedsConsent($consentRequired);
            return $options;
        } catch (\Exception $e) {
            $this->logger->error($e, "Failed to get tracking config");
            return false;
        }
    }
}
