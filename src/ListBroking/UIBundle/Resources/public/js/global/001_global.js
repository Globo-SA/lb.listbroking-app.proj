/**
 * Created by scastro on 11/10/14.
 */

(function ($, App) {
    $(function () {
        "use strict";

        // Select2 Ajax widgets
        $("[data-select-mode=local]").each(function(){
            $(this).select2();
        });

        $("[data-select-mode=ajax]").each(function () {

            // Current data type
            var $select = $(this);

            $select.select2({
                minimumInputLength: 0,
                minimumResultsForSearch: -1, //TODO: Fix the ajax search
                allowClear: true,
                ajax: {
                    url: App.routing.generate('ajax_lists'),
                    dataType: 'json',
                    data: function (term, page) {

                        // Ajax call data
                        var data = {};
                        data.type = $select.data('select-type');

                        // Parents data type (if parents exists)
                        var $parent = $($select.data('select-parent'));
                        if ($parent.length > 0) {
                            data.parent_type = $parent.data('select-type');
                            data.parent_id = $parent.select2('val');
                        }
                        //data.q = term;

                        return data;
                    },
                    results: function (data, page) {
                        return {results: data.response};
                    }
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
                }
            }).on('change', function (val, added, removed) {
                $($select.data('select-child')).select2('val', null);
            }).on('select2-clearing', function (val, added, removed) {
                $($select.data('select-child')).select2('val', null);
            });
        });

        // Datepickers
        $('[data-toggle=daterangepicker]').daterangepicker({
                ranges: {
                    '18-24 Years': [moment().subtract(24, 'years'), moment().subtract(18, 'years')],
                    '25-34 Years': [moment().subtract(34, 'years'), moment().subtract(25, 'years')],
                    '35-44 Years': [moment().subtract(44, 'years'), moment().subtract(35, 'years')],
                    '45-54 Years': [moment().subtract(54, 'years'), moment().subtract(45, 'years')],
                    '55-64 Years': [moment().subtract(64, 'years'), moment().subtract(55, 'years')],
                    '65-90 Years': [moment().subtract(90, 'years'), moment().subtract(65, 'years')]
                },
                startDate: moment().subtract('days', 29),
                endDate: moment()
            },
            function(start, end) {
                var range_start = moment().format('YYYY') - end.format('YYYY');
                var range_end = moment().format('YYYY') - start.format('YYYY');
                $('#filters_contact_details_birthdate_range').next('.help-block').html('Range: <strong>' + range_start + '-' + range_end + ' Years</strong>');
            });

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
                $label.data('placement', $el.data('placement'))
                $label.tooltip()
            }
        });
    });
})(jQuery, ListBroking)