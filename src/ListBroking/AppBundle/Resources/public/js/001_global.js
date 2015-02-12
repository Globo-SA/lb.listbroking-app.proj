/**
 * Created by scastro on 11/10/14.
 */

(function ($, App) {
    $(function () {
        "use strict";

        // If a tab is disabled dont activate it
        $('a[data-toggle="tab"]').on('click', function (e) {
            if($(this).parent('li').hasClass('disabled')){
                e.preventDefault();
                return false;
            }
        });

        // Extra animations
        jQuery.fn.extend({
            slide: function(direction, time) {
                return this.each(function() {
                    $(this).toggle('slide', {direction: direction}, time);
                });
            }
        });

        // Exception menu
        $('.exceptions_menu').click(function(e){
            e.preventDefault();

            // Clear old entities
            $('#exceptions_table tbody tr').remove();

            $('#loading_widget').fadeIn();
            $.ajax({
                type: "GET",
                url: App.routing.generate('last_exceptions'),
                dataType: 'json',
                success: function(data){
                    var response = data.response;
                    var $table = $('#exceptions_table');
                    var row = $table.data('prototype');

                    $.each(response, function(index, value){
                        var current_row = row.replace('%%code%%', value['code']);
                        var current_row = current_row.replace('%%created_at%%', value['created_at'].date.replace('.000000', ''));
                        var current_row = current_row.replace('%%msg%%', value['msg'].substring(0, 255) + '...');
                        $table.append(current_row);
                    });

                    $('.exceptions_menu').parent('li').addClass('open');

                    // Loading Widget, stop when everything is loaded
                    $('#loading_widget').fadeOut();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var response = jqXHR.responseJSON.response;
                    console.log(response);

                    // Loading Widget, stop when everything is loaded
                    $('#loading_widget').fadeOut();
                }
            });

            return false;
        });

        // Select2 widgets
        $("[data-select-mode=local]").each(function(){
            $(this).select2({allowClear: true});
        });

        $("[data-select-mode=open]").each(function(){
            $(this).select2({
                tags: [],
                allowClear: true
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
                $label.data('placement', $el.data('placement'));
                $label.tooltip()
            }
        });

        $("[data-mask]").inputmask();


        // Toggles elements text and disable state
        $.fn.toggleLoading = function(){
            var $trigger = $(this);
            var text = $trigger.html();
            var alt_text = $trigger.data('alt');

            $trigger.html(alt_text)
            $trigger.data('alt', text);

            if($trigger.is(':disabled')){
                $trigger.removeAttr('disabled');
            }else{
                $trigger.attr('disabled', 'disabled');
            }

            return $(this);
        };

        // Check for queues
        $.fn.checkQueues = function(type, key, value, callback){
            return setInterval(function(){
                $.ajax({
                    type: "GET",
                    url: App.routing.generate('ajax_taskcontroller_queue_check'),
                    dataType: 'json',
                    data: {type: type, key: key, value: value},
                    success: function(data){

                        var response = data.response;
                        if(response.response == 'ended'){
                            callback();
                        }

                    }
                });
            }, 5000);
        }

    });
})(jQuery, ListBroking);
