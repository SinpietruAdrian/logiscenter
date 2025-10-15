define([], function () {
    return function (target) {
        return target.extend({
            defaults: {
                template: 'Logiscenter_ImmutableQuote/shipping'
            },
            initialize: function () {
                this.isImmutableQuote = window.checkoutConfig.quoteData.is_immutable
                this._super();
            }
        });
    }
});
