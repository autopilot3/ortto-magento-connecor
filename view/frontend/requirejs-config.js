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
            add_to_cart: 'Ortto_Connector/js/add_to_cart'
        }
    }
};
