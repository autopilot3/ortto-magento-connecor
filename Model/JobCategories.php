<?php
declare(strict_types=1);


namespace Ortto\Connector\Model;

use Magento\Framework\Data\OptionSourceInterface;
use Ortto\Connector\Api\SyncCategoryInterface;

class JobCategories implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $categories = [
            SyncCategoryInterface::PRODUCT => "Products",
            SyncCategoryInterface::CUSTOMER => "Customers",
            SyncCategoryInterface::ORDER => "Orders",
            SyncCategoryInterface::STOCK_ALERT => "Back in stock subscriptions",
        ];

        $options = [];
        foreach ($categories as $key => $category) {
            $options[] = [
                'label' => $category,
                'value' => $key,
            ];
        }
        return $options;
    }
}
