/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */
define([
    'Magento_Ui/js/form/element/abstract'
], function (Element) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: false,
            modules: {
                parent: '${ $.parentName }'
            },
            imports: {
                watchedValue: '${ $.parentName }.is_immutable:value'
            },
            listens: {
                watchedValue: 'onWatchedValueChange'
            }
        },

        /**
         * Applies the filters collection once the watched value changes, ignoring the
         * initial sync that happens when this component links to the filter's value.
         *
         * @returns {void}
         */
        onWatchedValueChange: function () {
            if (!this.hasSeenInitialValue) {
                this.hasSeenInitialValue = true;
                return;
            }
            this.parent().apply();
        }
    });
});
