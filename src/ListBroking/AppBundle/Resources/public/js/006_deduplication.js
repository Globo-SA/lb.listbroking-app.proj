/**
 * Created by scastro on 31/10/14.
 */
(function ($, App)
{
    $(function() {
        "use strict";

        $('form[name=advanced_exclude]').submit(function(){

            var $input = $('#advanced_exclude_upload_file');
            var filename = getExtension($input.val());
            console.log(filename);
            if(!filename.match(/(xls|xlsx)/i)){
                $input.tooltip({
                    title: 'Must have a .xls or .xlsx extension'
                }).tooltip('show')

                setTimeout(function(){
                    $('#advanced_exclude_upload_file').tooltip('destroy')
                }, 2000);
            }
        });

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

        function getExtension(filename) {
            var parts = filename.split('.');
            return parts[parts.length - 1];
        }

    });
}
)(jQuery, ListBroking);