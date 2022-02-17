<?php

namespace Autopilot\AP3Connector\Api;

interface RoutesInterface
{
    // Magento Routes
    const MG_SYNC_CUSTOMERS = "autopilot/sync/customers";

    // Autopilot Routes
    const AP_AUTHENTICATE = "/-/installation/auth";
    const AP_UPDATE_ACCESS_TOKEN = "/magento/update-access-token";
    const AP_IMPORT_CONTACTS = "/magento/contact/merge-all";
}
