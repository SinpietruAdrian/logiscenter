require([
    'jquery',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'mage/translate',
    '!domReady'
], function ($, urlBuilder, customerData, $t) {
    $('.list-wrapper .quote span.activate').click(function () {
        const quoteId = $(this).data('quote-id');

        $.ajax({
            method: 'POST',
            url: urlBuilder.build('/rest/V1/immutable-quotes/quotes/' + quoteId +'/enable'),
            dataType: 'json',
            global: false,
            success: function (response) {
                if (response === true) {
                    customerData.reload(['cart'], true).done(function () {
                        showSystemMessage('success', $t('Quote activated successfully. The page will be refreshed shortly'));
                        setTimeout(function () {
                            window.location.reload();
                        }, 2000)
                    });
                }
            },
            error: function (xhr) {
                const message = xhr.responseJSON.message;
                showSystemMessage('error', message);
            }
        });
    });

    const showSystemMessage = function (type, message) {
        customerData.set('messages', {
            messages: [{
                text: message,
                type: type
            }]
        });
    }
});
