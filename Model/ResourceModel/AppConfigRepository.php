<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Model\ResourceModel;

use Autopilot\AP3Connector\Api\AppConfigRepositoryInterface;
use Autopilot\AP3Connector\Api\Data\AppConfigInterface;
use Autopilot\AP3Connector\Helper\Config;
use Autopilot\AP3Connector\Logger\AutopilotLoggerInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
use Magento\Store\Model\StoreManagerInterface;

class AppConfigRepository implements AppConfigRepositoryInterface
{
    private AutopilotLoggerInterface $logger;
    private WriterInterface $configWriter;
    private StoreManagerInterface $storeManager;
    private Pool $cacheFrontendPool;

    /**
     * @param AutopilotLoggerInterface $logger
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        AutopilotLoggerInterface $logger,
        WriterInterface $configWriter,
        StoreManagerInterface $storeManager,
        Pool $cacheFrontendPool
    ) {
        $this->logger = $logger;
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * @throws Exception
     * @throws LocalizedException
     */
    public function update(AppConfigInterface $config)
    {
        $this->logger->debug("Configuration Request Received", $config->toArray());

        $trackingCode = $config->getTrackingCode();
        if (empty($trackingCode)) {
            throw new Exception(__('Tracking code cannot be empty'), 400);
        }

        $trackingJsURL = $config->getTrackingJSUrl();
        if (empty($trackingJsURL)) {
            throw new Exception(__('Tracking URL cannot be empty'), 400);
        }

        $captureURL = $config->getCaptureUrl();
        if (empty($captureURL)) {
            throw new Exception(__('Capture URL cannot be empty'), 400);
        }

        $instanceId = $config->getInstanceId();
        if (empty($instanceId)) {
            throw new Exception(__('Instance ID cannot be empty'), 400);
        }

        $scopeType = $config->getScopeType();
        $scopeId = $config->getScopeId();
        switch (strtolower($scopeType)) {
            case 'website':
                // Throws 404 if website was not found
                $this->storeManager->getWebsite($scopeId);
                $scopeType = "websites";
                break;
            case 'store':
                // Throws 404 if store was not found
                $this->storeManager->getStore($scopeId);
                $scopeType = "stores";
                break;
            default:
                throw new Exception(__(sprintf('Invalid scope type %s', $scopeType)), 400);
        }

        $this->configWriter->save(
            Config::XML_PATH_TRACKING_CODE,
            $trackingCode,
            $scopeType,
            $scopeId
        );
        $this->configWriter->save(
            Config::XML_PATH_TRACKING_URL,
            $trackingJsURL,
            $scopeType,
            $scopeId
        );
        $this->configWriter->save(
            Config::XML_PATH_CAPTURE_URL,
            $captureURL,
            $scopeType,
            $scopeId
        );
        $this->configWriter->save(
            Config::XML_PATH_INSTANCE_ID,
            $config->getInstanceId(),
            $scopeType,
            $scopeId
        );
        $this->cacheFrontendPool->get('config')->clean();
    }
}
