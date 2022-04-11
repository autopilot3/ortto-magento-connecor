<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Config extends AbstractHelper
{
    // Scope config paths
    const XML_PATH_BASE_URL = "autopilot/general/base_url";
    const XML_PATH_CLIENT_ID = "autopilot/general/client_id";
    const XML_PATH_TRACKING_ENABLED = "ap_general/tracking/enabled";
    const XML_PATH_ACTIVE = "ap_general/authentication/active";
    const XML_PATH_API_KEY = "ap_general/authentication/api_key";
    const XML_PATH_TRACKING_CODE = "ap_general/tracking/code";
    const XML_PATH_TRACKING_URL = "ap_general/tracking/js_url";
    const XML_PATH_CAPTURE_URL = "ap_general/tracking/capture_url";
    const XML_PATH_INSTANCE_ID = "ap_general/general/instance_id";

    const XML_PATH_SYNC_CUSTOMER_AUTO_ENABLED = "ap_sync/customer/auto_sync_enabled";
    const XML_PATH_SYNC_ORDER_AUTO_ENABLED = "ap_sync/order/auto_sync_enabled";
    const XML_PATH_SYNC_CUSTOMER_NON_SUBSCRIBED_ENABLED = "ap_sync/customer/non_subscribed_enabled";
    const XML_PATH_SYNC_ANONYMOUS_ORDERS_ENABLED = "ap_sync/order/anonymous_enabled";
    const XML_PATH_IMAGE_PLACE_HOLDER = 'catalog/placeholder/image_placeholder';
    const XML_PATH_SMALL_IMAGE_PLACE_HOLDER = 'catalog/placeholder/small_image_placeholder';
    const XML_PATH_SWATCH_IMAGE_PLACE_HOLDER = 'catalog/placeholder/swatch_image_placeholder';
    const XML_PATH_THUMBNAIL_IMAGE_PLACE_HOLDER = 'catalog/placeholder/thumbnail_placeholder';

    // "2006-01-02T15:04:05Z07:00"
    const DATE_TIME_FORMAT = 'Y-m-d\TH:i:sP';
    const DB_DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    const EMPTY_DATE_TIME = "0001-01-01T00:00:00Z";
}
