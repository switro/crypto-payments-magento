define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'switro_cryptopayments',
                component: 'Switro_CryptoPayments/js/view/payment/method-renderer/payment'
            }
        );
        return Component.extend({});
    }
);