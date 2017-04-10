/**
 * Created by scastro on 31/10/14.
 */
(function ($, App) {
    $(function () {
        "use strict";
        // Get and save extraction id as a global variable
        var $extraction_deduplication_form = $("form[name=extraction_deduplication]");
        var $deduplication_btn = $('#deduplication_download');
        var $extraction_deduplication_upload_button = $("#extraction_deduplication_upload_button");
        var extraction_template = $("#deduplication_template");
        var $deduplication_modal = $('#lead_deduplication_modal');

        $deduplication_modal.on("show.bs.modal", function(ev){
            $("#extraction_deduplication_remove_old_deduplication").iCheck("uncheck");
        });

        // Publishes the extraction for delivering
        $deduplication_btn.on('click', function (e) {
            $deduplication_btn.attr('disabled', 'disabled').find('i').fadeIn();
            $.ajax({
                type: "POST",
                url: App.routing.generate('ajax_extraction_deliver', {extraction_id: App.variables.extractionId, extraction_template_id: extraction_template.select2('val')}),
                dataType: 'json',
                success: function (data) {
                }
            });
        });

        //Stop submitting because it will never be used
        $extraction_deduplication_form.submit(function (e) {
            e.preventDefault();
            return false;
        });

        //jQuery-Fileupload will take care of submitting
        $extraction_deduplication_upload_button.on('click', function () {
            var $this = $(this);
            var data = $this.data();

            //Check if there's a file before submitting
            if (!$.isEmptyObject(data)) {
                $extraction_deduplication_upload_button.find('i.loading').fadeIn();

                $('#extraction_deduplication_upload_file').attr('required', false);
                data.submit()
            }
        });

        // Uploads the deduplication file and handles loading
        $('#extraction_deduplication_upload_file').fileupload({
            url: ListBroking.routing.generate('ajax_extraction_deduplication', {extraction_id: $extraction_deduplication_form.data('extraction')}),
            dataType: 'json',
            autoUpload: false,
            removeAfterUpload: false,
            add: function (e, data) {
                var acceptFileTypes = /\.(csv)/i;
                var $errors = $('#fileuploaderror');
                var $extraction_deduplication_btn = $('#extraction_deduplication_upload_button');

                if (data.originalFiles[0]['name'].length && !acceptFileTypes.test(data.originalFiles[0]['name'])) {
                    $extraction_deduplication_upload_button.find('i.loading').fadeOut();
                    $errors
                        .html('Only csv files are accepted')
                        .fadeIn()
                    ;
                    $extraction_deduplication_btn.attr('disabled', 'disabled');
                } else {
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
                $('.progress-bar').css('width', '0%');

                $extraction_deduplication_upload_button.data(data);
            })
            .on('fileuploadprogressall', function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                var $progress = $('.progress');
                if ($progress.hasClass('hidden')) {
                    $progress.removeClass('hidden');
                }
                $('.progress-bar').css(
                    'width',
                    progress + '%'
                );
            })
            .on('fileuploaddone', function (e, data) {

                $extraction_deduplication_upload_button.find('i.loading').fadeOut();

                //Close modal
                $deduplication_modal.modal('hide');

                // Set loading on trigger
                $('#lead_deduplication_trigger')
                    .attr('disabled', 'disabled')
                    .find('i.loading').fadeIn();

                $.listenToExtractionChanges(function (extraction) {

                });
            })
        ;
    });
})(jQuery, ListBroking);