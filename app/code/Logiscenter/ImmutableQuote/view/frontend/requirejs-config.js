var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Logiscenter_ImmutableQuote/js/mixins/shipping': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'Logiscenter_ImmutableQuote/js/mixins/billing-address': true
            },
            'Magento_Checkout/js/view/shipping-address/address-renderer/default': {
                'Logiscenter_ImmutableQuote/js/mixins/shipping-address/renderer': true
            }
        }
    }
};
