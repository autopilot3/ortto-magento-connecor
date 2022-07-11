<?php
declare(strict_types=1);

namespace Ortto\Connector\Model\Data;

use Magento\Framework\DataObject;
use Ortto\Connector\Api\Data\OrttoGiftInterface;

class OrttoGift extends DataObject implements OrttoGiftInterface
{
    /** @inheirtDoc */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /** @inheirtDoc */
    public function getMessage()
    {
        return (string)$this->getData(self::MESSAGE);
    }

    /** @inheirtDoc */
    public function setSender($sender)
    {
        return $this->setData(self::SENDER, $sender);
    }

    /** @inheirtDoc */
    public function getSender()
    {
        return (string)$this->getData(self::SENDER);
    }

    /** @inheirtDoc */
    public function setRecipient($recipient)
    {
        return $this->setData(self::RECIPIENT, $recipient);
    }

    /** @inheirtDoc */
    public function getRecipient()
    {
        return (string)$this->getData(self::RECIPIENT);
    }

    /** @inheirtDoc */
    public function serializeToArray()
    {
        if ($this == null) {
            return null;
        }
        $result=[];
        $result[self::MESSAGE] = $this->getMessage();
        $result[self::SENDER] = $this->getSender();
        $result[self::RECIPIENT] = $this->getRecipient();
        return $result;
    }
}
