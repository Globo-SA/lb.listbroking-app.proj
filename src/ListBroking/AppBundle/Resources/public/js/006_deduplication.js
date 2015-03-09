/**
 * Created by scastro on 31/10/14.
 */
(function ($, App)
{
    $(function() {
        "use strict";
        // Get and save extraction id as a global variable
        var $extraction_deduplication_form = $("[name=extraction_deduplication]");

        // Downloads de extraction for deduplication
        $('#deduplication-download').on('click', function(e){
            e.preventDefault();

            var extraction_template = $(this).prev('input').select2('val');
            if(extraction_template){
                var url = Routing.generate('ajax_extraction_download', {extraction_id: $(this).data('extraction'), extraction_template_id: extraction_template});
                $(this).find('i').fadeIn();
                setTimeout(function(){
                    $('#deduplication-download').find('i').fadeOut();
                },5000);
                window.location = url;
            }

        });

        //Stop submitting because it will never be used
        $extraction_deduplication_form.submit(function(e){
            e.preventDefault();
            return false;
        });

        //jQuery-Fileupload will take care of submitting
        $("#extraction_deduplication_upload_button").on('click', function () {
            var $this = $(this);
            var data = $this.data();

            //Check if there's a file before submitting
            if (!$.isEmptyObject(data)) {
                $('#extraction_deduplication_upload_file').attr('required', false);
                data.submit()
            }
        });

        $('#extraction_deduplication_upload_file').fileupload({
            url: ListBroking.routing.generate('ajax_deduplication', {extraction_id:  $extraction_deduplication_form.data('extraction')}),
            dataType: 'json',
            autoUpload: false,
            removeAfterUpload: false,
            add: function(e, data) {
                var acceptFileTypes = /\.(csv)/i;
                var $errors = $('#fileuploaderror');
                var $extraction_deduplication_btn= $('#extraction_deduplication_upload_button');

                if(data.originalFiles[0]['name'].length && !acceptFileTypes.test(data.originalFiles[0]['name'])) {
                    $errors
                        .html('Only csv files are accepted')
                        .fadeIn()
                    ;
                    $extraction_deduplication_btn.attr('disabled', 'disabled');
                }else{
                    $errors.fadeOut();
                    $extraction_deduplication_btn.attr('disabled', null);
                }
            }
        })
        .on('fileuploadadd', function (e, data) {
            $.each(data.files, function (index, file) {
                $('#files').html(file.name);
            });

            //Reset progress-bar
            $('.progress').addClass('hidden');
            $('.progress-bar').css('width','0%');

            $("#extraction_deduplication_upload_button").data(data);
        })
        .on('fileuploadprogressall', function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                var $progress = $('.progress');
                if($progress.hasClass('hidden')){
                    $progress.removeClass('hidden');
                }
                $('.progress-bar').css(
                    'width',
                    progress + '%'
                );
        })
        .on('fileuploaddone', function (e, data) {
                $('#lead_deduplication_trigger').toggleLoading();

                //Close modal
                $('#lead_deduplication_modal').modal('hide');

                // Start checking for deduplication end
                App.variables.deduplicationQueueId = $('body').checkQueues('deduplication_queue', 'value1', App.variables.extractionId, function(){
                    // Stop interval
                    clearInterval(App.variables.deduplicationQueueId);

                    $('body').refreshContacts();

                    // Stop loading button
                    $('#lead_deduplication_trigger').toggleLoading();
                });
        })
        ;

        // If the button is disabled there were Queues when
        // the page was rendered
       if($('#lead_deduplication_trigger').is(':disabled')){

           // Start checking for deduplication end
           App.variables.deduplicationQueueId = $('body').checkQueues('deduplication_queue', 'value1', App.variables.extractionId, function(){
               // Stop interval
               clearInterval(App.variables.deduplicationQueueId);

               $('body').refreshContacts();

               // Stop loading button
               $('#lead_deduplication_trigger').toggleLoading();
           });
       }
    });
}
)(jQuery, ListBroking);