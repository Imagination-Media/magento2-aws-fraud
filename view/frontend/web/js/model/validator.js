/**
 * Amazon Fraud integration (https://aws.amazon.com/fraud-detector/?nc1=h_ls)
 *
 * Use AWS Fraud to detect fake customers
 *
 * @package     ImaginationMedia\AwsFraud
 * @author      Igor Ludgero Miura <igor@imaginationmedia.com>
 * @copyright   Copyright (c) 2020 Imagination Media (https://www.imaginationmedia.com/)
 * @license     https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'jquery/ui',
        'mage/translate',
    ],
    function ($, alert, fullScreenLoader, quote) {
        'use strict';
        return {
            /**
             * Validate if applied gift card amount is valid.
             *
             * @returns {boolean}
             */
            validate: function() {
                var result = true;
                var checkUrl = window.checkoutConfig.aws_fraud.validator;

                $.ajax({
                    url: checkUrl,
                    type: "POST",
                    data: {
                        customer_email: (quote.hasOwnProperty('guestEmail')) ? quote.guestEmail : ''
                    },
                    success: function (response) {
                        result = response.result;
                        if (!result) {
                            alert({
                                title: $.mage.__('Fraud'),
                                content: '<p>'+$.mage.__("You order was declined. Please contact us for more info.")+'</p>',
                                actions: {
                                    always: function(){
                                        console.log('Close the fraud modal');
                                    }
                                }
                            });
                        }
                    },
                    error: function (xhr) {
                        alert({
                            title: $.mage.__('Fraud validation'),
                            content: $.mage.__("We weren't able to check if this is a fraud or not.")
                        });
                    },
                    async: false
                });

                return result;
            }
        }
    }
);
