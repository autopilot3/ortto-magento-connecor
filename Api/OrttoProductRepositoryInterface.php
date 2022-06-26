<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface OrttoProductRepositoryInterface
{
    public const LOAD_CATEGORIES = "load_categories";
    public const PRODUCT_ID_LIST = 'product_id_list';
    public const PRODUCT_SKU_LIST = 'product_sku_list';

    /**
     * @param ConfigScopeInterface $scope
     * @param int $page
     * @param string $checkpoint
     * @param int $pageSize
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListProductResponseInterface
     */
    public function getList(
        ConfigScopeInterface $scope,
        int $page,
        string $checkpoint,
        int $pageSize,
        array $data = []
    );

    /**
     * Returns the list of products by IDs. The returned array is keyed by product ID.
     * In case any product was not found, the value for the key will be null.
     * @param ConfigScopeInterface $scope
     * @param int[] $productIds
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListProductResponseInterface
     */
    public function getByIds(ConfigScopeInterface $scope, array $productIds, array $data = []);

    /**
     * Returns the list of products by SKUs. The returned array is keyed by SKU.
     * In case any product was not found, the value for the key will be null.
     * @param ConfigScopeInterface $scope
     * @param string[] $productSKUs
     * @param array $data
     * @return \Ortto\Connector\Api\Data\ListProductResponseInterface
     */
    public function getBySKUs(ConfigScopeInterface $scope, array $productSKUs, array $data = []);
}
