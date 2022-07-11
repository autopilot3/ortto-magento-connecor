<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoGiftInterface
{
    const MESSAGE = 'message';
    const SENDER = 'sender';
    const RECIPIENT = 'recipient';

    /**
    * Set message
    *
    * @param string $message
    * @return $this
    */
    public function setMessage($message);

    /**
    * Get message
    *
    * @return string
    */
    public function getMessage();

    /**
    * Set sender
    *
    * @param string $sender
    * @return $this
    */
    public function setSender($sender);

    /**
    * Get sender
    *
    * @return string
    */
    public function getSender();

    /**
    * Set recipient
    *
    * @param string $recipient
    * @return $this
    */
    public function setRecipient($recipient);

    /**
    * Get recipient
    *
    * @return string
    */
    public function getRecipient();

    /**
    * Convert object data to array
    *
    * @return array
    */
    public function serializeToArray();
}
