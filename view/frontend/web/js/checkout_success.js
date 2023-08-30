define([], function () {
    "use strict";
    return function (config) {
        window.addEventListener("load", function () {
            const ap3c = window.ap3c;
            /**
             * Initializer
             * @param {Function} ap3c.trackMagento
             * @param {Object} config.payload
             * @param {String} config.email
             * @param {String} config.phone
             */
            if (ap3c && ap3c.trackMagento) {
                ap3c.trackMagento(config.email, config.phone, config.payload)
            }
        });
    }
})
