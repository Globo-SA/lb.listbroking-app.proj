/**
 * Created by scastro on 11/10/14.
 */

(function ($, App) {
    $(function () {
        "use strict";

        // If a tab is disabled dont activate it
        $('a[data-toggle="tab"]').on('click', function (e) {
            if ($(this).parent('li').hasClass('disabled')) {
                e.preventDefault();
                return false;
            }
        });

        // Exception menu
        $('.exceptions_menu').click(function (e) {
            e.preventDefault();

            // Clear old entities
            $('#exceptions_table').find('tbody tr').remove();

            $('#loading_widget').fadeIn();
            $.ajax({
                type: "GET",
                url: App.routing.generate('ajax_last_exceptions'),
                dataType: 'json',
                success: function (data) {
                    var response = data.response;
                    var $table = $('#exceptions_table');
                    var row = $table.data('prototype');

                    $.each(response, function (index, value) {
                        var current_row = row.replace('%%code%%', value['code']);
                        current_row = current_row.replace('%%created_at%%', value['created_at'].date.replace('.000000', ''));
                        current_row = current_row.replace('%%msg%%', value['msg'].substring(0, 255) + '...');
                        $table.append(current_row);
                    });

                    $('.exceptions_menu').parent('li').addClass('open');

                    // Loading Widget, stop when everything is loaded
                    $('#loading_widget').fadeOut();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    var response = jqXHR.responseJSON.response;
                    console.log(response);

                    // Loading Widget, stop when everything is loaded
                    $('#loading_widget').fadeOut();
                }
            });

            return false;
        });

        // Simple dataTable instance
        $('[data-toggle=simple_table]').dataTable({
            bPaginate: false,
            bFilter: false,
            bSort: true,
            bInfo: false
        });

        /**
         * Extra jQuery animations
         */
        $.fn.extend({
            slide: function (direction, time) {
                return this.each(function () {
                    $(this).toggle('slide', {direction: direction}, time);
                });
            }
        });

        /**
         * Toggles elements text and disable state
         * @returns {*|HTMLElement}
         */
        $.fn.toggleLoading = function () {
            var $trigger = $(this);
            var text = $trigger.html();
            var alt_text = $trigger.data('alt');

            $trigger.html(alt_text);
            $trigger.data('alt', text);

            return $(this);
        };
    });
})(jQuery, ListBroking);
