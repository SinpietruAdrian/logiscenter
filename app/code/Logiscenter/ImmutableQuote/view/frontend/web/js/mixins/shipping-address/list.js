define([], function () {
    return function (target) {
        var defaultRendererTemplate = {
            parent: '${ $.$data.parentName }',
            name: '${ $.$data.name }',
            component: 'Magento_Checkout/js/view/shipping-address/address-renderer/default',
            provider: 'checkoutProvider'
        };

        return target.extend({
            createRendererComponent: function (address, index) {
                var rendererTemplate, templateData, rendererComponent;

                if (index in this.rendererComponents) {
                    this.rendererComponents[index].address(address);
                } else {
                    // rendererTemplates are provided via layout
                    rendererTemplate = address.getType() != undefined && this.rendererTemplates[address.getType()] != undefined ?
                        utils.extend({}, defaultRendererTemplate, this.rendererTemplates[address.getType()]) :
                        defaultRendererTemplate;
                    templateData = {
                        parentName: this.name,
                        name: index
                    };
                    rendererComponent = utils.template(rendererTemplate, templateData);
                    utils.extend(rendererComponent, {
                        address: ko.observable(address)
                    });
                    layout([rendererComponent]);
                    this.rendererComponents[index] = rendererComponent;
                }
            }
        });
    }
});
