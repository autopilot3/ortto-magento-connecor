<?php
declare(strict_types=1);


namespace Ortto\Connector\Controller;

use Ortto\Connector\Logger\OrttoLoggerInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\UrlInterface;

abstract class AbstractJsonController
{
    private JsonFactory $jsonFactory;
    private OrttoLoggerInterface $logger;

    private const DEFAULT_MESSAGE = 'Internal Server Error';
    private const MESSAGE_TAG = 'message';

    /**
     * @var RequestInterface $request
     */
    private RequestInterface $request;

    /**
     * @var UrlInterface $url
     */
    private UrlInterface $url;

    protected function __construct(
        Context $context,
        OrttoLoggerInterface $logger
    ) {
        $this->jsonFactory = new JsonFactory($context->getObjectManager());
        $this->logger = $logger;
        $this->request = $context->getRequest();
        $this->url = $context->getUrl();
    }

    /**
     * @return Json
     */
    protected function error(
        string $message,
        \Exception $e = null,
        $data = null
    ) {

        if (empty($message)) {
            $message = self::DEFAULT_MESSAGE;
        }
        if (empty($e)) {
            $this->logger->warn($message, $data);
        } else {
            $this->logger->error($e, $message);
        }
        return $this->jsonFactory->create()->setData([
            'error' => true,
            self::MESSAGE_TAG => $message,
        ]);
    }

    /**
     * @return Json
     */
    protected function success($data)
    {
        return $this->jsonFactory->create()->setData($data);
    }

    /**
     * @return Json
     */
    protected function successMessage(string $message)
    {
        return $this->jsonFactory->create()->setData([
            self::MESSAGE_TAG => $message,
        ]);
    }

    /** @return RequestInterface */
    protected function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Build url by requested path and parameters
     *
     * @param string|null $routePath
     * @param array|null $routeParams
     * @return  string
     */
    protected function getUrl($routePath = null, $routeParams = null): string
    {
        return $this->url->getUrl($routePath, $routeParams);
    }
}
