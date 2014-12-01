/**
 * Created by scastro on 11/10/14.
 */

(function ($, App) {
    $(function () {
        "use strict";

        // Extra animations
        jQuery.fn.extend({
            slide: function(direction, time) {
                return this.each(function() {
                    $(this).toggle('slide', {direction: direction}, time);
                });
            }
        });

        // Select2 widgets
        $("[data-select-mode=local]").each(function(){
            $(this).select2();
        });

        $("[data-select-mode=open]").each(function(){
            $(this).select2({
                tags: [],
            });
        });

        $("[data-select-mode=ajax]").each(function () {

            // Current data type
            var $select = $(this);

            $select.select2({
                minimumInputLength: 0,
                minimumResultsForSearch: -1, //TODO: Fix the ajax search
                allowClear: true,
                ajax: {
                    url: App.routing.generate('ajax_form_lists'),
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

        $("[data-mask]").inputmask();

    });
})(jQuery, ListBroking)
