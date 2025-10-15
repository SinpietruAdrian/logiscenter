define([], function () {
    return function (target) {
        return target.extend({
            defaults: {
                template: 'Logiscenter_ImmutableQuote/billing-address',
                detailsTemplate: 'Logiscenter_ImmutableQuote/billing-address/details',
            },
            initialize: function () {
                this.isImmutableQuote = window.checkoutConfig.quoteData.is_immutable
                this._super();
            }
        });
    }
});
