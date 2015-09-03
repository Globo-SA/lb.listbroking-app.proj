/**
 * Created by scastro on 11/10/14.
 */

(function ($, App) {
    $(function () {
        "use strict";

        // Ping the system for errors
        var $ping_modal = $('#ping_modal');
        setInterval(function () {
            $.ajax({
                type: "GET",
                url: App.routing.generate('ajax_ping'),
                dataType: 'json',
                statusCode: {
                    200: function () {
                        $ping_modal.modal('hide');
                    },
                    500: function () {
                        if (!$ping_modal.is(':visible')) {
                            $('#ping_modal_login_error').fadeIn();
                            $('#ping_modal_login_form').fadeOut();

                            $ping_modal.modal();
                        }
                    },
                    403: function () {
                        if (!$ping_modal.is(':visible')) {
                            $('#ping_modal_login_form').fadeIn();
                            $('#ping_modal_login_error').fadeOut();

                            $ping_modal.modal();
                        }
                    }
                }
            });
        }, App.variables.intervalTimeout);

        // Ajax Login system
        var $ajax_login_form = $('#ajax_login_form');
        var $ajax_login_form_error = $('#ajax_login_form_error');
        $ajax_login_form.submit(function (e) {
            e.preventDefault();

            $ajax_login_form.find('button')
                .attr('disabled', 'disabled')
                .find('i.loading').fadeIn();
            $.ajax({
                type: $ajax_login_form.attr('method'),
                url: $ajax_login_form.attr('action'),
                data: $ajax_login_form.serialize(),
                dataType: "json",
                success: function (data, status, object) {
                    // Fadeout and enable the button
                    $ajax_login_form.find('button')
                        .removeAttr('disabled')
                        .find('i.loading').fadeOut();
                    $('#loading_widget').fadeOut();

                    if (!data.success) {
                        $ajax_login_form_error.html(data.message);
                        return
                    }

                    $ajax_login_form_error.html('Login successful, you can continue playing!');
                    // Close the modal

                    setTimeout(function(){
                        $ping_modal.modal('hide');
                    }, 2000);
                },
                error: function (data, status, object) {
                    console.log(data.message);
                }
            });
        });

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
