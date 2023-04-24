<?php
declare(strict_types=1);

namespace Ortto\Connector\Helper;

class Config
{
    // Scope config paths
    const XML_PATH_BASE_URL = "ortto/general/base_url";
    const XML_PATH_CLIENT_ID = "ortto/general/client_id";
    const XML_PATH_TRACKING_ENABLED = "ortto_general/tracking/enabled";
    const XML_PATH_ACTIVE = "ortto_general/authentication/active";
    const XML_PATH_API_KEY = "ortto_general/authentication/api_key";
    const XML_PATH_TRACKING_CODE = "ortto_general/tracking/code";
    const XML_PATH_CAPTURE_JS_URL = "ortto_general/tracking/capture_js_url";
    const XML_PATH_MAGENTO_CAPTURE_JS_URL = "ortto_general/tracking/magento_js_url";
    const XML_PATH_CAPTURE_API_URL = "ortto_general/tracking/capture_url";
    const XML_PATH_INSTANCE_ID = "ortto_general/general/instance_id";
    const XML_PATH_LOGGING_VERBOSE = "ortto_general/logging/verbose";

    const XML_PATH_IMAGE_PLACE_HOLDER = 'catalog/placeholder/image_placeholder';
    const XML_PATH_SMALL_IMAGE_PLACE_HOLDER = 'catalog/placeholder/small_image_placeholder';
    const XML_PATH_SWATCH_IMAGE_PLACE_HOLDER = 'catalog/placeholder/swatch_image_placeholder';
    const XML_PATH_THUMBNAIL_IMAGE_PLACE_HOLDER = 'catalog/placeholder/thumbnail_placeholder';

    const ALL_KEYS = [
        "is_active" => self::XML_PATH_ACTIVE,
        "base_url" => self::XML_PATH_BASE_URL,
        "client_id" => self::XML_PATH_CLIENT_ID,
        "capture_enabled" => self::XML_PATH_TRACKING_ENABLED,
        "tracking_code" => self::XML_PATH_TRACKING_CODE,
        "capture_js" => self::XML_PATH_CAPTURE_JS_URL,
        "magento_capture_js" => self::XML_PATH_MAGENTO_CAPTURE_JS_URL,
        "capture_api" => self::XML_PATH_CAPTURE_API_URL,
        "instance_id" => self::XML_PATH_INSTANCE_ID,
        "verbose" => self:: XML_PATH_LOGGING_VERBOSE,
        "image_place_holder" => self::XML_PATH_IMAGE_PLACE_HOLDER,
        "small_image_place_holder" => self::XML_PATH_SMALL_IMAGE_PLACE_HOLDER,
        "swatch_place_holder" => self::XML_PATH_SWATCH_IMAGE_PLACE_HOLDER,
        "thumbnail_place_holder" => self::XML_PATH_THUMBNAIL_IMAGE_PLACE_HOLDER,
    ];


    // "2006-01-02T15:04:05Z07:00"
    const DATE_TIME_FORMAT = 'Y-m-d\TH:i:sP';
    const DB_DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    const EMPTY_DATE_TIME = "0001-01-01T00:00:00Z";
    const EMPTY_DATE = "0001-01-01";

    // Capture Events
    const EVENT_TYPE_WAITING_ON_STOCK = 'product_waiting_on_stock';
    const EVENT_TYPE_PRODUCT_ADDED_TO_CART = 'product_added_to_cart';
    const EVENT_TYPE_PRODUCT_VIEWED = 'product_viewed';
    const EVENT_TYPE_CHECKOUT_STARTED = 'checkout_started';
    const EVENT_TYPE_ORDER_CREATED = 'order_created';
}
