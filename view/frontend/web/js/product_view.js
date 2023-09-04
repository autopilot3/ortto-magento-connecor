define([], function () {
    "use strict";
    return function (config) {
        const ap3c = window.ap3c;
        if (ap3c && ap3c.trackMagento) {
            /**
             * Initializer
             * @param {Function} ap3c.trackMagento
             * @param {Object} config.payload
             * @param {String} config.email
             * @param {String} config.phone
             */
            ap3c.trackMagento(config.email, config.phone, config.payload)
        }
    }
})
