var config = {
    config: {
        mixins: {
            'Magento_Catalog/js/catalog-add-to-cart': {
                'Ortto_Connector/js/catalog-add-to-cart-mixin': true
            },
        }
    },
    map: {
        '*': {
            product_view: 'Ortto_Connector/js/product_view',
            checkout: 'Ortto_Connector/js/checkout',
            checkout_success: 'Ortto_Connector/js/checkout_success',
            add_to_cart: 'Ortto_Connector/js/add_to_cart'
        }
    }
};
