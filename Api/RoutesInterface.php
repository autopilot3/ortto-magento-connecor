<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

interface RoutesInterface
{
    // Magento Routes
    const MG_SYNC_CUSTOMERS = "autopilot/sync/customers";
    const MG_SYNC_ORDERS = "autopilot/sync/orders";
    const MG_SYNC_PRODUCTS = "autopilot/sync/products";

    // Autopilot Routes
    const AP_AUTHENTICATE = "/-/installation/auth";
    const AP_IMPORT_CONTACTS = "/magento/contact/merge-all";
    const AP_IMPORT_ORDERS = "/magento/contact/orders";
    const AP_IMPORT_PRODUCTS = '/magento/products/import';
}
