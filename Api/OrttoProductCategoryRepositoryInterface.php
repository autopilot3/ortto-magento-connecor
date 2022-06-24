<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface OrttoProductCategoryRepositoryInterface
{
    /**
     * @param int $categoryId
     * @return \Ortto\Connector\Api\Data\OrttoProductCategoryInterface|bool
     */
    public function getById(int $categoryId);
}
