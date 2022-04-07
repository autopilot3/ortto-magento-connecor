define(
    ['jquery', 'Magento_Ui/js/modal/alert'],
    function ($, popup) {
        'use strict'
        const modal = ({url, title, w, h}) => {
            const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
            const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;

            const width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth
                ? document.documentElement.clientWidth : screen.width;
            const height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight
                ? document.documentElement.clientHeight : screen.height;

            const systemZoom = width / window.screen.availWidth;
            const left = (width - w) / 2 / systemZoom + dualScreenLeft
            const top = (height - h) / 2 / systemZoom + dualScreenTop
            const options = `scrollbars=yes,width=${w / systemZoom},height=${h / systemZoom},top=${top},left=${left}`
            const newWindow = window.open(url, title, options);

            if (window.focus) {
                newWindow.focus();
            }
        }
        /**
         * Initializer
         * @param {String} config.url
         * @param {String} config.client_id
         * @param {String} config.title
         * @param {Object} config.data
         */
        return function (config, element) {
            $('#' + element.id).click(function (event) {
                event.preventDefault();
                if (!config.data.client_id) {
                    config.data['form_key'] = window.FORM_KEY;
                }
                $.ajax({
                    url: config.url,
                    type: "POST",
                    contentType: 'application/x-www-form-urlencoded',
                    data: config.data,
                    /**
                     * Initializer
                     * @param {String} data.redirect_uri
                     * @param {String} data.error
                     * @param {String} data.message
                     */
                    success: function (data) {
                        if (data.redirect_uri) {
                            modal({url: data.redirect_uri, title: config.title, w: 500, h: 600});
                        } else {
                            let title = config.title;
                            if (data.error) {
                                title = 'Error';
                            }
                            popup({title: title, content: data.message});
                        }
                    },
                    error: function (jqXHR) {
                        if (jqXHR !== null && jqXHR !== undefined && (jqXHR.status === 400 || jqXHR.status === 401)) {
                            let message = 'Invalid Request';
                            if (jqXHR.responseJSON !== null && jqXHR.responseJSON !== undefined &&
                                jqXHR.responseJSON.message !== null && jqXHR.responseJSON.message !== undefined) {
                                message = jqXHR.responseJSON.message;
                            }
                            popup({title: "Error", content: message});
                        } else {
                            popup({title: "Error", content: "Internal Server Error"});
                        }
                    }
                });
            });
        };
    }
);
