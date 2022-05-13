<?php
declare(strict_types=1);


namespace Ortto\Connector\Controller\Adminhtml;

use Ortto\Connector\Logger\OrttoLoggerInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

abstract class AbstractBackendJsonController extends Action
{
    private JsonFactory $jsonFactory;
    private OrttoLoggerInterface $logger;

    private const DEFAULT_MESSAGE = 'Internal Server Error';
    private const MESSAGE_TAG = 'message';

    protected function __construct(
        Context $context,
        OrttoLoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->jsonFactory = new JsonFactory($context->getObjectManager());
        $this->logger = $logger;
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
}
