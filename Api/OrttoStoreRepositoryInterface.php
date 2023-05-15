<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\OrttoStoreInterface;

interface OrttoStoreRepositoryInterface
{
    /**
     * @param ConfigScopeInterface $scope
     * @param int $storeId
     * @return OrttoStoreInterface|null
     */
    public function getById(
        ConfigScopeInterface $scope,
        int $storeId
    );
}
