<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

interface RoutesInterface
{
    // Magento Routes
    const MG_SYNC_CUSTOMERS = "autopilot/sync/customers";
    const MG_SYNC_ORDERS = "autopilot/sync/orders";
    const MG_SYNC_PRODUCTS = "autopilot/sync/products";
    const MG_PRODUCT_VIEW = "autopilot/product/view";

    // Autopilot Routes
    const AP_AUTHENTICATE = "/-/installation/auth";
    const AP_IMPORT_CONTACTS = "/magento/contact/merge-all";
    const AP_IMPORT_ORDERS = "/magento/contact/orders";
    const AP_PRODUCT_VIEW = "/magento/product/view";
    const AP_IMPORT_PRODUCTS = '/magento/products/import';
}
