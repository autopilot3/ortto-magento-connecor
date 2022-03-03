define(
    ['jquery', 'Magento_Ui/js/modal/alert'],
    function ($, popup) {
        'use strict'
        /**
         * Initializer
         * @param {String} config.url
         * @param {String} config.scope_type
         * @param {Number} config.scope_id
         * @param {String} config.client_id
         */
        return function (config, element) {
            $('#' + element.id).click(function (event) {
                event.preventDefault();
                const body = {
                    'form_key': window.FORM_KEY,
                    "scope_type": config.scope_type,
                    "scope_id": config.scope_id,
                    "client_id": config.client_id
                }
                $.ajax({
                    url: config.url,
                    type: "POST",
                    contentType: 'application/x-www-form-urlencoded',
                    data: body,
                    success: function (data) {
                        let title = 'Info';
                        if (data.error) {
                            title = 'Error';
                        }
                        popup({title: title, content: data.message});
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
