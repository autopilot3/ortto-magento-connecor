<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

interface OrttoProductParentGroupInterface
{
    const BUNDLE = 'bundle';
    const CONFIGURABLE = 'configurable';
    const GROUPED = 'grouped';

    /**
     * Set bundle
     *
     * @param int[] $bundle
     * @return $this
     */
    public function setBundle(array $bundle);

    /**
     * Get bundle
     *
     * @return int[]
     */
    public function getBundle(): array;

    /**
     * Set configurable
     *
     * @param int[] $configurable
     * @return $this
     */
    public function setConfigurable(array $configurable);

    /**
     * Get configurable
     *
     * @return int[]
     */
    public function getConfigurable(): array;

    /**
     * Set grouped
     *
     * @param int[] $grouped
     * @return $this
     */
    public function setGrouped(array $grouped);

    /**
     * Get grouped
     *
     * @return int[]
     */
    public function getGrouped(): array;
}
