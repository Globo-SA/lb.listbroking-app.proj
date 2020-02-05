/**
 * Created by scastro on 28/05/15.
 */

(function ($, App) {
    $(function () {
        "use strict";

        // Select2 widgets - LOCAL
        $("[data-select-mode=local]").each(function () {
            $(this).select2({
                allowClear: true,
                placeholder: $(this).data('placeholder')
            });
        });

        // Select2 widgets - OPEN INPUT
        $("[data-select-mode=open]:not(#filters_location_contact\\:postalcode1\\:integer\\:basic\\:inclusion)").each(function () {
            $(this).select2({
                tags: [],
                allowClear: true,
                placeholder: $(this).data('placeholder'),
                multiple: true
            });
        });

        // Select2 widgets - AJAX
        $("[data-select-mode=ajax]").each(function () {

            // Current data type
            var $select = $(this);

            $select.select2({
                minimumInputLength: $select.data('select-minimum-input'),
                multiple: $select.data('select-multiple'),
                allowClear: true,
                ajax: {
                    url: App.routing.generate('ajax_form_lists'),
                    dataType: 'json',
                    data: function (term, page) {

                        // Ajax call data
                        var data = {};
                        data.type = $select.data('select-type');

                        data.q = term;

                        return data;
                    },
                    results: function (data, page) {
                        return {results: data.response};
                    },
                    cache: true
                },
                initSelection: function (element, callback) {
                    var data = {
                        type: $(element).data('select-type'),
                        id: $(element).val().split(',')
                    };
                    $.ajax({
                        type: "GET",
                        url: App.routing.generate('ajax_form_lists'),
                        data: data,
                        success: function (data) {
                            var result = data.response;
                            if(result.length == 1){
                                result = result[0];
                            }

                            callback(result);
                        }
                    });
                },
                formatResult: function (item) {
                    var markup = "";
                    if (item.name !== undefined) {
                        markup += "<option value='" + item.id + "'>" + item.name + "</option>";
                    }
                    return markup;
                },
                formatSelection: function (item) {
                    return item.name
                },
                    formatSelectionCssClass: function(tag, container) {
                        console.log($(container));
                    $(container).parent().addClass("my-css-class");
                }
            });
        });

        // Datepickers
        $('[data-toggle=datepicker]').daterangepicker({
            singleDatePicker: true,
            startDate: moment()
        });

        // Fix tooltips on checkboxes
        $("[data-toggle='tooltip'][type='checkbox']").each(function () {
            var $el = $(this);
            var $label = $el.parents('.form-group').find('label');
            if ($label.length > 0) {
                $(this).tooltip('destroy');
                $label.attr('title', $el.data('original-title'));
                $label.data('placement', $el.data('placement'));
                $label.tooltip()
            }
        });

        // Input masks
        $("[data-mask]").inputmask();
    });
})(jQuery, ListBroking);