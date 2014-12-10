/**
 * Created by scastro on 09/12/14.
 */
(function ($, App)
{
    $(function() {
        "use strict";

        // Confirm that the Extraction is final before submit
        $("#confirm_extraction_chk").on('ifChanged', function(){
            $("#confirm_extraction_btn").toggleClass('disabled');
        });

        // Downloads de extraction for delivery
        $('#delivery-download').on('click', function(e){
            e.preventDefault();

            var extraction_template = $(this).prev('input').select2('val');
            if(extraction_template){
                var url = Routing.generate('ajax_extraction_download', { extraction_id: App.variables.extractionId, extraction_template_id: extraction_template });
                $(this).find('i.ion-loading-c').fadeIn();
                setTimeout(function(){
                    $('#delivery-download').find('i.ion-loading-c').fadeOut();

                },3000);
                window.location = url;
            }
        });
        $('#delivery-modal-send').click(function(){
            var emails = $(this).parent().find('input.emails').select2('val');
            var extraction_template = $(this).parent().find('input.extraction_template').select2('val');

            if(emails.length > 0 && extraction_template > 0){
                $(this).find('i.ion-loading-c').fadeIn();
                $.ajax({
                    type: "POST",
                    url: App.routing.generate('ajax_extraction_deliver', { extraction_id: App.variables.extractionId, extraction_template_id: extraction_template } ),
                    dataType: 'json',
                    data: {emails: emails},
                    success: function(data){

                        $('#delivery-modal-send').find('i.ion-loading-c').fadeOut();
                    }
                });
            }

        });

    });
}
)(jQuery, ListBroking);