/**
 * Created by scastro on 31/10/14.
 */
(function ($, App)
{
    $(function() {
        "use strict";

        //Stop submitting because it will never be used
        var $extraction_deduplication_form = $("[name=extraction_deduplication]");
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
                var $trigger = $('#lead_internal_deduplication_trigger');
                var text = $trigger.html();
                var alt_text = $trigger.data('alt');

                //Close modal
                $('#lead_internal_deduplication_modal').modal('hide');
                $trigger
                    .attr('disabled', 'disabled')
                    .html(alt_text)
                ;
                $trigger.data(text);

                //TODO: Add spiner to check if its done
        })
        ;

        $('#internal-deduplication-download, #external-deduplication-download').on('click', function(e){
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

        function getExtension(filename) {
            var parts = filename.split('.');
            return parts[parts.length - 1];
        }

    });
}
)(jQuery, ListBroking);