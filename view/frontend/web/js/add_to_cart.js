define(['jquery'], function ($) {
    "use strict";
    return function (config) {
        const ap3c = window.ap3c;
        /**
         * Initializer
         * @param {Function} ap3c.trackMagento
         * @param {Object} config.payload
         * @param {String} config.email
         * @param {String} config.phone
         */
        if (ap3c && ap3c.trackMagento) {
            $.ajax({
                url: '/ortto/cart/get',
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
