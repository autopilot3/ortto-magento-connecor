<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface SyncCategoryInterface
{
    // NOTE: Update Models\JobCategories when this list is changed
    const CUSTOMER = "customer";
    const ORDER = "order";
    const PRODUCT = "product";
    const STOCK_ALERT = "stock_alert";
}
