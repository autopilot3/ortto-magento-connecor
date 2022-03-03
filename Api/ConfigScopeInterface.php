<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;

interface ConfigScopeInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @return bool
     */
    public function isAutoCustomerSyncEnabled(): bool;

    /**
     * @return bool
     */
    public function isNonSubscribedCustomerSyncEnabled(): bool;

    /**
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * @return string
     */
    public function getAPIKey(): string;

    /**
     * Returns all the store IDs under a website if scope type == 'website', otherwise (type == 'store')
     * only the ID of the current store will be included.
     *
     * @return int[]
     */
    public function getStoreIds(): array;

    /**
     * @return string
     */
    public function getAccessToken(): string;

    public function equals(ConfigScopeInterface $scope): bool;

    /**
     * @param string $type
     * @param int $id
     * @return void
     * @throws LocalizedException|NotFoundException|NoSuchEntityException
     */
    public function load(string $type, int $id);

    /**
     * @return int
     */
    public function getWebsiteId(): int;

    /**
     * @return string
     */
    public function toString(): string;
}
