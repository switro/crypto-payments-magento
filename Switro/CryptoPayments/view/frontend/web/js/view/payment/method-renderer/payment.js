define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/url'
    ],
    function ($, Component, fullScreenLoader, mageUrl) {
        'use strict';

        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Switro_CryptoPayments/payment/template'
            },
            afterPlaceOrder: function () {
                fullScreenLoader.stopLoader();
                $.mage.redirect(
                    mageUrl.build('switro/payment/redirect')
                );
                return false;
            }
        });
    }
);