<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface SyncCategoryInterface
{
    const CUSTOMER = "customer";
    const ORDER = "order";
    const PRODUCT = "product";
    const STOCK_ALERT = "stock_alert";
}
