/**
 * Created by scastro on 18/06/15.
 */
(function ($, App) {
    $(function () {
        "use strict";

        // Delivery Modal
        $('#delivery_modal_send').click(function () {
            var extraction_template = $("#delivery_extraction_template").select2('val') ;
            var $delivery_error = $('#delivery_error');

            if (extraction_template == '') {
                $delivery_error.fadeIn();
                return;
            }

            $delivery_error.fadeOut();
            $(this).find('i.loading').fadeIn();
            $.ajax({
                type: "POST",
                url: App.routing.generate('ajax_extraction_deliver', {extraction_id: App.variables.extractionId, extraction_template_id: extraction_template}),
                dataType: 'json',
                success: function (data) {

                    $('#delivery_modal_send').find('i.loading').fadeOut();

                    //Close modal
                    $('#delivery_modal').modal('hide');

                    // Set loading on trigger
                    $('#deliver_extraction_trigger')
                        .attr('disabled', 'disabled')
                        .find('i.loading').fadeIn()
                    ;
                }
            });

        });
    });
})(jQuery, ListBroking);