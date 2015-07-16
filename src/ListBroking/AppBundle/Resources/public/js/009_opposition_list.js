/**
 * Created by scastro on 07/01/15.
 */
(function ($, App) {
    $(function () {
        "use strict";

        var $opposition_list_import_form = $("[name=opposition_list_import]");
        var $opposition_list_import_trigger = $("#opposition_list_import_trigger");
        var $opposition_list_import_btn = $("#opposition_list_import_submit");

        //Stop submitting because it will never be used
        $opposition_list_import_form.submit(function(e){
            e.preventDefault();
            return false;
        });

        //jQuery-Fileupload will take care of submitting
        $opposition_list_import_btn.on('click', function () {
            var $this = $(this);
            var data = $this.data();

            //Check if there's a file before submitting
            if (!$.isEmptyObject(data)) {
                $('#opposition_list_import_upload_file').attr('required', false);
                data.submit()
            }
        });

        $('#opposition_list_import_upload_file').fileupload({
            url: ListBroking.routing.generate('ajax_opposition_list_import'),
            dataType: 'json',
            autoUpload: false,
            removeAfterUpload: false,
            add: function(e, data) {
                var acceptFileTypes = /\.(xls|xlsx)/i;
                var $errors = $('#fileuploaderror');

                if(data.originalFiles[0]['name'].length && !acceptFileTypes.test(data.originalFiles[0]['name'])) {
                    $errors
                        .html('Only xls files are accepted')
                        .fadeIn()
                    ;
                    $opposition_list_import_btn.attr('disabled', 'disabled');
                }else{
                    $errors.fadeOut();
                    $opposition_list_import_btn.attr('disabled', null);
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

            $opposition_list_import_btn.data(data);
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
            checkIsImporting();

            //Close modal
            $('#opposition_list_import_modal').modal('hide');
        })
        ;

        checkIsImporting();
        setInterval(function(){
            checkIsImporting();
        }, App.variables.intervalTimeout);

        function checkIsImporting(){
            $.ajax({
                type: "GET",
                url: App.routing.generate('ajax_opposition_list_import_check'),
                dataType: 'json',
                success: function (data) {
                    var response = data.response;
                    if(!response.importing){
                        $opposition_list_import_trigger
                            .removeAttr('disabled')
                            .html('Import List');

                        // Loading Widget, stop when everything is loaded
                        $('#loading_widget').fadeOut();

                        return;
                    }

                    $opposition_list_import_trigger
                        .attr('disabled', 'disabled')
                        .html("<i class='icon icon-large ion-loading-c'></i>&nbsp;Importing lists...");

                }
            });
        }
    });
})(jQuery, ListBroking);
