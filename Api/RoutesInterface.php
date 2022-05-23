<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface RoutesInterface
{
    // Magento Routes
    const MG_SYNC_CUSTOMERS = "ortto/sync/customers";
    const MG_SYNC_ORDERS = "ortto/sync/orders";
    const MG_SYNC_PRODUCTS = "ortto/sync/products";
    const MG_SYNC_STOCK_ALERTS = "ortto/sync/stockalerts";
    const MG_CART_GET = "ortto/cart/get";

    // Ortto Routes
    const ORTTO_AUTHENTICATE = "/-/installation/auth";
    const ORTTO_IMPORT_CONTACTS = "/magento/contact/merge-all";
    const ORTTO_IMPORT_ORDERS = "/magento/contact/orders";
    const ORTTO_IMPORT_PRODUCTS = '/magento/products/import';
    const ORTTO_IMPORT_WAITING_ON_STOCK = '/magento/products/waiting-on-stock';
}
