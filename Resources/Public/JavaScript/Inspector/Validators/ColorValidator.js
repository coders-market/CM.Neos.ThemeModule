/**
 * Validator for color hex/rgb/rgba values.
 */
define(
    [
        'Shared/Validation/AbstractValidator',
        'Shared/I18n'
    ],
    function(AbstractValidator, I18n) {
        return AbstractValidator.extend({
            /**
             * Checks if the given value is a valid hex, rgb or rgba color.
             *
             * @param {string} value The value that should be validated
             * @return {void}
             */
            isValid: function(value) {

                if(!/^#(?:[0-9a-f]{3}){1,2}$/i.test(value)) {

                    if (!/^rgb\s*\((\s*[012]?[0-9]{1,2}\s*,\s*[012]?[0-9]{1,2}\s*,\s*[012]?[0-9]{1,2}\s*)\)$/i.test(value)) {
                        if (!/^rgba\s*\((\s*[012]?[0-9]{1,2}\s*,\s*[012]?[0-9]{1,2}\s*,\s*[012]?[0-9]{1,2}\s*,\s*(0(\.\d+)?|1(\.0+)?)\s*)\)$/i.test(value)) {
                            this.addError('A valid color (hex, rgb, rgba) is expected.');
                            this.addError(I18n.translate('content.inspector.validators.hexValidator.aValidColorIsExpected'));
                        }
                    }
                }
            }
        });
    }
);