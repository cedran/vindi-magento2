define([
    'underscore',
    'ko',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Payment/js/model/credit-card-validation/credit-card-data',
    'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/action/cancel-coupon',
    'Magento_Catalog/js/price-utils',
    'mage/translate',
    'jquery',
    'mageUtils'
], function (
    _,
    ko,
    Component,
    creditCardData,
    cardNumberValidator,
    quote,
    totals,
    setCouponCodeAction,
    cancelCouponCodeAction,
    priceUtils,
    $t,
    $,
    utils
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Vindi_Payment/payment/vindi',
            paymentProfiles: [],
            creditCardType: '',
            creditCardExpYear: '',
            creditCardExpMonth: '',
            creditCardNumber: '',
            creditCardOwner: '',
            creditCardSsStartMonth: '',
            creditCardSsStartYear: '',
            creditCardVerificationNumber: '',
            selectedPaymentProfile: null,
            selectedCardType: null,
            selectedInstallments: null,
            creditCardInstallments: ko.observableArray([]),
            maxInstallments: 1
        },
        getData: function () {
            var data = {
                'method': this.getCode(),
                'additional_data': {
                    'payment_profile': this.selectedPaymentProfile(),
                    'cc_type': this.selectedCardType(),
                    'cc_exp_year': this.creditCardExpYear(),
                    'cc_exp_month': this.creditCardExpMonth(),
                    'cc_number': this.creditCardNumber(),
                    'cc_owner': this.creditCardOwner(),
                    'cc_ss_start_month': this.creditCardSsStartMonth(),
                    'cc_ss_start_year': this.creditCardSsStartYear(),
                    'cc_cvv': this.creditCardVerificationNumber(),
                    'cc_installments': this.selectedInstallments() ? this.selectedInstallments() : 1
                }
            };

            return data;
        },
        initObservable: function () {
            var self = this;

            this._super()
                .observe([
                    'creditCardType',
                    'creditCardExpYear',
                    'creditCardExpMonth',
                    'creditCardNumber',
                    'creditCardOwner',
                    'creditCardVerificationNumber',
                    'creditCardSsStartMonth',
                    'creditCardSsStartYear',
                    'selectedCardType',
                    'selectedPaymentProfile',
                    'selectedInstallments',
                    'maxInstallments'
                ]);

            setCouponCodeAction.registerSuccessCallback(function () {
                self.updateInstallments();
            });

            cancelCouponCodeAction.registerSuccessCallback(function () {
                self.updateInstallments();
            });

            this.checkPlanInstallments();

            return this;
        },

        validate: function () {
            if (this.selectedPaymentProfile() == null || this.selectedPaymentProfile() == '') {
                if (!this.selectedCardType() || this.selectedCardType() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the Credit Card Type.')});
                    return false;
                }

                if (!this.creditCardExpYear() || this.creditCardExpYear() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the Credit Card Expiry Year.')});
                    return false;
                }

                if (!this.creditCardExpMonth() || this.creditCardExpMonth() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the Credit Card Expiry Month.')});
                    return false;
                }

                if (!this.creditCardNumber() || this.creditCardNumber() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the Credit Card Number.')});
                    return false;
                }

                if (!this.creditCardOwner() || this.creditCardOwner() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the Credit Card Owner Name.')});
                    return false;
                }

                if (!this.creditCardVerificationNumber() || this.creditCardVerificationNumber() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the Credit Card CVV.')});
                    return false;
                }
            }

            if (this.installmentsAllowed()) {
                if (!this.selectedInstallments() || this.selectedInstallments() == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter the number of Installments.')});
                    return false;
                }
            } else {
                this.selectedInstallments(1);
            }

            return true;
        },

        initialize: function () {
            var self = this;
            this._super();

            self.updateInstallments();

            // Set credit card number to credit card data object
            this.creditCardNumber.subscribe(function (value) {
                var result;

                if (value == '' || value == null) {
                    return false;
                }

                result = cardNumberValidator(value);

                if (!result.isPotentiallyValid && !result.isValid) {
                    return false;
                }

                if (result.isValid) {
                    creditCardData.creditCardNumber = value;
                }
            });

            this.creditCardOwner.subscribe(function (value) {
                creditCardData.creditCardOwner = value;
            });

            // Set expiration year to credit card data object
            this.creditCardExpYear.subscribe(function (value) {
                creditCardData.expirationYear = value;
            });

            // Set expiration month to credit card data object
            this.creditCardExpMonth.subscribe(function (value) {
                creditCardData.expirationYear = value;
            });

            // Set cvv code to credit card data object
            this.creditCardVerificationNumber.subscribe(function (value) {
                creditCardData.cvvCode = value;
            });
            this.selectedInstallments.subscribe(function (value) {
                creditCardData.selectedInstallments = value;
            });
        },

        isActive: function () {
            return true;
        },

        getCcAvailableTypes: function () {
            return window.checkoutConfig.payment.vindi_cc.availableTypes['vindi_cc'];
        },

        getCcMonths: function () {
            return window.checkoutConfig.payment.vindi_cc.months['vindi_cc'];
        },

        getCcYears: function () {
            return window.checkoutConfig.payment.vindi_cc.years['vindi_cc'];
        },

        hasVerification: function () {
            return window.checkoutConfig.payment.vindi_cc.hasVerification['vindi_cc'];
        },

        getCcAvailableTypesValues: function () {
            return _.map(this.getCcAvailableTypes(), function (value, key) {
                return {
                    'value': key,
                    'type': value
                };
            });
        },
        getCcMonthsValues: function () {
            return _.map(this.getCcMonths(), function (value, key) {
                return {
                    'value': key,
                    'month': value
                };
            });
        },
        getCcYearsValues: function () {
            return _.map(this.getCcYears(), function (value, key) {
                return {
                    'value': key,
                    'year': value
                };
            });
        },
        getCcTypesValues: function () {
            return _.map(this.getCcAvailableTypes(), function (value, key) {
                return {
                    'value': key,
                    'name': value
                };
            });
        },
        installmentsAllowed: function () {
            let isAllowed = parseInt(window.checkoutConfig.payment.vindi_cc.isInstallmentsAllowedInStore);
            return isAllowed !== 0 ? true : false;
        },
        updateInstallments: function (maxInstallments = null) {
            let self = this;
            let ccCheckoutConfig = window.checkoutConfig.payment.vindi_cc;
            let installments = [];

            if (ccCheckoutConfig) {
                let allowInstallments = ccCheckoutConfig.isInstallmentsAllowedInStore;
                let maxInstallmentsNumber = maxInstallments || ccCheckoutConfig.maxInstallments;
                let minInstallmentsValue = ccCheckoutConfig.minInstallmentsValue;

                let grandTotal = totals.getSegment('grand_total').value;
                if (maxInstallmentsNumber > 1 && self.installmentsAllowed()) {
                    let installmentsTimes = Math.floor(grandTotal / minInstallmentsValue);

                    for (let i = 1; i <= maxInstallmentsNumber; i++) {
                        let value = Math.ceil((grandTotal / i) * 100) / 100;
                        installments.push({
                            'value': i,
                            'text': `${i} de ${self.getFormattedPrice(value)}`
                        });

                        if (i + 1 > installmentsTimes) {
                            break;
                        }
                    }
                } else {
                    installments.push({
                        'value': 1,
                        'text': `1 de ${self.getFormattedPrice(grandTotal)}`
                    });
                }
            }
            self.creditCardInstallments(installments);
        },
        getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, quote.getPriceFormat());
        },

        getPaymentProfiles: function () {
            let paymentProfiles = [];
            const savedCards = window.checkoutConfig.payment?.vindi_cc?.saved_cards;

            if (savedCards) {
                savedCards.forEach(function (card) {
                    paymentProfiles.push({
                        'value': card.id,
                        'text': `${card.card_type.toUpperCase()} xxxx-${card.card_number}`
                    });
                });
            }

            return paymentProfiles;
        },

        hasPaymentProfiles: function () {
            return this.getPaymentProfiles().length > 0;
        },

        checkPlanInstallments: function () {
            var self = this;
            $.ajax({
                url: self.getUrl('vindi_vr/plan/get'),
                type: 'GET',
                success: function (response) {
                    if (response && response.installments) {
                        self.maxInstallments(response.installments);
                        self.updateInstallments(response.installments);
                    } else {
                        self.updateInstallments();
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching plan installments:', error);
                    self.updateInstallments();
                }
            });
        },

        getUrl: function (path) {
            var url = window.BASE_URL + path;
            return url;
        }
    });
});
