/**
 * Created by scastro on 19/01/15.
 */
(function ($, App) {
    $(function () {
        "use strict";

        var $staging_contact_import_form = $("[name=staging_contact_import]");
        var $staging_contact_import_btn = $("#staging_contact_import_submit");

        //Stop submitting because it will never be used
        $staging_contact_import_form.submit(function(e){
            e.preventDefault();
            return false;
        });

        //jQuery-Fileupload will take care of submitting
        $staging_contact_import_btn.on('click', function () {
            var $this = $(this);
            var data = $this.data();

            //Check if there's a file before submitting
            if (!$.isEmptyObject(data)) {
                $('#staging_contact_import_upload_file').attr('required', false);
                data.submit()
            }
        });

        $('#staging_contact_import_upload_file').fileupload({
            url: ListBroking.routing.generate('ajax_staging_contact_import'),
            dataType: 'json',
            autoUpload: false,
            removeAfterUpload: false,
            add: function(e, data) {
                var acceptFileTypes = /\.(xls|xlsx)/i;
                var $errors = $('#fileuploaderror');

                if(data.originalFiles[0]['name'].length && !acceptFileTypes.test(data.originalFiles[0]['name'])) {
                    $errors
                        .html('Only excel files are accepted')
                        .fadeIn()
                    ;
                    $staging_contact_import_btn.attr('disabled', 'disabled');
                }else{
                    $errors.fadeOut();
                    $staging_contact_import_btn.attr('disabled', null);
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

                $staging_contact_import_btn.data(data);
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
                $('#staging_contact_import_trigger').toggleLoading();

                //Close modal
                $('#staging_contact_import_modal').modal('hide');
            })
        ;
    });
})(jQuery, ListBroking);
