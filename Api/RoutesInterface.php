<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

interface RoutesInterface
{
    // Magento Routes
    const MG_SYNC_CUSTOMERS = "ortto/sync/customers";
    const MG_SYNC_ORDERS = "ortto/sync/orders";
    const MG_SYNC_PRODUCTS = "ortto/sync/products";
    const MG_CART_GET = "ortto/cart/get";

    // Autopilot Routes
    const AP_AUTHENTICATE = "/-/installation/auth";
    const AP_IMPORT_CONTACTS = "/magento/contact/merge-all";
    const AP_IMPORT_ORDERS = "/magento/contact/orders";
    const AP_IMPORT_PRODUCTS = '/magento/products/import';
}
