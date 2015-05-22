/**
 * Created by scastro on 09/12/14.
 */
(function ($, App)
{
    $(function() {
        "use strict";

        var $finalize_btn =  $("#finalize-extraction-btn");

        // Confirm that the Extraction is final before submit
        $("#confirm-extraction-chk").on('ifChanged', function(){
            $("#confirm-extraction-btn").toggleClass('disabled');
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

        // Check if there is at least 1 lock_type selected for
        // enabling the finalize button
        //$('[name=lock_chk]').on('change', function(){
        //    if($('[name=lock_chk]:checked').length > 0){
        //        $finalize_btn.removeClass('disabled');
        //    }else{
        //        $finalize_btn.addClass('disabled');
        //    }
        //});
        $finalize_btn.removeClass('disabled');

        // Generate the locks and redirect to the last step
        $finalize_btn.click(function(){

            $(this).find('i.ion-loading-c').fadeIn();

            // Map all selected lock types
            var lock_types = $('[name=lock_chk]:checked').map(function(){
                return parseInt($(this).val());
            }).get();

            if(lock_types.length == 0 && !confirm('Do you really want to continue without locking the contacts ?')) {
                return false;
            }

            // Send locks to the server
            $.ajax({
                type: "POST",
                url: App.routing.generate('ajax_extraction_locks', { extraction_id: App.variables.extractionId } ),
                dataType: 'json',
                data: {lock_types: lock_types},
                success: function(data){

                    var $btn =  $('#finalize-extraction-btn');
                    window.location =  $btn.data('url');
                }
            });
        });


    });
}
)(jQuery, ListBroking);