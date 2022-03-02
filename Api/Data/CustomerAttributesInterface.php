<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api\Data;

interface CustomerAttributesInterface
{
    public const ID = 'id';
    public const CONTACT_ID = 'contact_id';
    public const CUSTOMER_ID = 'customer_id';

    /**
     * @return string
     */
    public function getAutopilotContactId(): string;

    /**
     * @param string $contactId
     * @return $this
     */
    public function setAutopilotContactId(string $contactId);

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId);

    /**
     * @return int
     */
    public function getCustomerId(): int;
}
