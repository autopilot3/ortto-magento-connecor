<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoCustomerGroupInterface
{
    const REGISTERED = 'registered';
    const ANONYMOUS = 'anonymous';

    /**
     * Set registered customers
     *
     * @param \Ortto\Connector\Api\Data\OrttoCustomerInterface[] $customers
     * @return $this
     */
    public function setRegistered($customers);

    /**
     * Get registered customers
     *
     * @return \Ortto\Connector\Api\Data\OrttoCustomerInterface[]
     */
    public function getRegistered();

    /**
     * Set anonymous customers
     *
     * @param \Ortto\Connector\Api\Data\OrttoCustomerInterface[] $customers
     * @return $this
     */
    public function setAnonymous($customers);

    /**
     * Get anonymous customers
     *
     * @return \Ortto\Connector\Api\Data\OrttoCustomerInterface[]
     */
    public function getAnonymous();
}
