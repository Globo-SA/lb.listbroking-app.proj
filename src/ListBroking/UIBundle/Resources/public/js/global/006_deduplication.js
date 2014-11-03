/**
 * Created by scastro on 31/10/14.
 */
(function ($, App)
{
    $(function() {
        "use strict";

        $('#deduplication-download').on('click', function(e){
            e.preventDefault();

            var extraction_template = $(this).prev('input').select2('val');
            if(extraction_template){
                var url = Routing.generate('extraction_download', {extraction_id: $(this).data('extraction'), extraction_template_id: extraction_template});
                $(this).find('i').fadeIn();
                setTimeout(function(){
                    $('#deduplication-download').find('i').fadeOut();
                },5000);
                window.location = url;
            }

        });

    });
}
)(jQuery, ListBroking);