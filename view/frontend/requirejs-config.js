var config = {
    config: {
        mixins: {
            'Magento_Catalog/js/catalog-add-to-cart': {
                'Autopilot_AP3Connector/js/catalog-add-to-cart-mixin': true
            },
        }
    },
    map: {
        '*': {
            product_view: 'Autopilot_AP3Connector/js/product_view',
            checkout: 'Autopilot_AP3Connector/js/checkout',
            checkout_success: 'Autopilot_AP3Connector/js/checkout_success',
            add_to_cart: 'Autopilot_AP3Connector/js/add_to_cart'
        }
    }
};
