define(['jquery'], function ($) {
    "use strict";
    return function (config) {
        const ap3c = window.ap3c;
        const ortto = window.ortto;
        /**
         * Initializer
         * @param {Function} ap3c.trackMagento
         * @param {Object} config.payload
         * @param {String} config.email
         * @param {String} config.phone
         * @param {String} config.url
         */
        if (ap3c && ap3c.trackMagento && ortto && ortto.base_url) {
            $.ajax({
                url: ortto.base_url.concat('/ortto/cart/get'),
                type: "GET",
                data: config,
                success: function (resp) {
                    if (resp.error) {
                        return;
                    }
                    ap3c.trackMagento(config.email, config.phone, resp);
                }
            });
        }
    }
})
