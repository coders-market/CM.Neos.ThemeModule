/**
 * Created by kapale on 08.05.17.
 */
$( document ).ready(function () {
    var fonts = {},
        savedValues = [],
        _$formSelects = $('.cm-neos-thememodule-custom-font-family-selector'),
        htmlCheckboxOpen = '<div class="checkbox"><label>',
        htmlCheckboxClose = '</label></div>';

    function buildHtmlSubVarContainer(type,label,inputs) {
        return '<div class="'+type+'"><label>'+label+'</label>' + inputs + '</div>';
    }

    function buildHtmlCheckbox(value, itemId, index, checked) {
        return htmlCheckboxOpen + '<input type="checkbox" name="customSettings[font][type][font][' + itemId + '][value][variants]['+index+']" value="' + value + '"' + (checked === true ? ' checked' : '') + '> ' + value + htmlCheckboxClose;
    }

    function buildHtml(font, itemId) {
        var htmlCheckboxesVariants = '',
            variantLabel = '',
            htmlCheckboxesVariantsContainer = '';

        if(font) {
            var _$variantsContainer = $('#' + itemId + '-root .variants .jq-container');

            // Render font variants checkboxes
            font.variants.forEach(function (element, index) {
                htmlCheckboxesVariants += buildHtmlCheckbox(element, itemId, index)
            });
            // Only show if at least one checkbox is available
            if(font.variants.length > 0) {
                variantLabel = _$variantsContainer.attr('data-label');
                htmlCheckboxesVariantsContainer = buildHtmlSubVarContainer('variants',variantLabel,htmlCheckboxesVariants);
            }
            _$variantsContainer.empty().append(htmlCheckboxesVariantsContainer);

        } else {

            savedValues.forEach(function (item) {
                var _$variantsContainer = $('#' + item.id + '-root .variants .jq-container'),
                    htmlCheckboxesVariantsContainer = '',
                    htmlCheckboxesVariants = '';


                function findFont(font) {
                    return font.family === item.family;
                }

                var fontDetails = fonts[item.category].find(findFont),
                    checked = false,
                    savedValueVariantsLenght = 0;

                if (typeof item.variants !== 'undefined' && item.variants !== null) {
                    savedValueVariantsLenght = item.variants.length;
                }

                // Render Variants
                fontDetails.variants.forEach(function (element, index) {

                    if (savedValueVariantsLenght > 0) {
                        item.variants.includes(element) ? checked = true : checked = false;
                    }
                    htmlCheckboxesVariants += buildHtmlCheckbox(element, item.id, index, checked);
                });

                if(fontDetails.variants.length > 0) {
                    variantLabel = _$variantsContainer.attr('data-label');
                    htmlCheckboxesVariantsContainer = buildHtmlSubVarContainer('variants',variantLabel,htmlCheckboxesVariants);
                }
                _$variantsContainer.off();
                _$variantsContainer.empty().append(htmlCheckboxesVariantsContainer);
                _$variantsContainer.on('change',function () {
                    var hiddenVariants = [];

                    $(this).parent('.variants').find('input').each(function () {
                        if(this.checked) {
                            hiddenVariants.push($(this).val());
                        }
                    });

                    $(this).parents('.font-extra-root').find('.cm-neos-thememodule-custom-font-family-hidden.variants').val(JSON.stringify(hiddenVariants));
                });
            });
        }

    }

    function getDataFromForm() {
        _$formSelects.each(function () {
            var _$this = $(this),
                selectId = _$this.attr('id'),
                selectCategory = _$this.attr('data-category'),
                _$selectedCategoryJsonFontElement = $('#' + selectId + '-root');

            fonts[selectCategory] = JSON.parse(_$selectedCategoryJsonFontElement.attr('data-json-fonts'));

            var variantsJson = _$selectedCategoryJsonFontElement.find('.variants').val();
            if(variantsJson !== 'null' && variantsJson !== null && typeof variantsJson !== 'undefined' && variantsJson.length > 4) {
                var variants = JSON.parse(variantsJson);
            }

            var savedValue = {
                "id": selectId,
                "family": _$this.val(),
                "category": selectCategory,
                "variants": (variants ? variants : [])
            };

            savedValues.push(savedValue);

        });
    }

    function bindOnChangeToSelects () {
        _$formSelects.on('change', function () {
            var _$this = $(this),
                selectId = _$this.attr('id'),
                selectCategory = _$this.attr('data-category'),
                selectedFont = _$this.val(),
                fontDetails;

            function findFont(font) {
                return font.family === selectedFont;
            }

            fontDetails = fonts[selectCategory].find(findFont);

            _$this.parent().find('.cm-neos-thememodule-custom-font-family-hidden.variants').val(null);

            buildHtml(fontDetails,selectId);
        });
    }

    function init () {

        getDataFromForm();

        buildHtml();

        bindOnChangeToSelects();
    }

    init();
});
