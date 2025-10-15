define([], function () {
    return function (target) {
        return target.extend({
            defaults: {
                template: 'Logiscenter_ImmutableQuote/shipping-address/renderer/default'
            },
            initialize: function () {
                this.isImmutableQuote = window.checkoutConfig.quoteData.is_immutable
                this._super();
            }
        });
    }
});
