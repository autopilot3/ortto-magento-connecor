define([
        "jquery"
    ], function ($) {
        "use strict";
        return function (config) {
            $.ajax({
                url: config.url,
                type: "POST",
                contentType: 'application/x-www-form-urlencoded',
                data: {
                    "product_id": config.product_id,
                    "customer_id": config.customer_id,
                    "store_id": config.store_id,
                }
            });
        }
    }
)
