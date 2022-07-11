<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\OrttoCartInterface;

interface OrttoCartRepositoryInterface
{
    /**
     * @param ConfigScopeInterface $scope
     * @param int $cartId
     * @param array $data
     * @return OrttoCartInterface
     */
    public function getById(ConfigScopeInterface $scope, int $cartId, array $data = []);
}
