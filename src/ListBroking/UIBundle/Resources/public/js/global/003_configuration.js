/**
 * Created by scastro on 11/10/14.
 */

(function ($, App)
{
    $(function() {
        "use strict";

        $('#existing_form_trigger').on('click',function(){
            if(!$(this).find('input').is(':checked')){
                $("#new_form").fadeOut(function () {
                    $("#existing_form").fadeIn();
                });
            }
        });
        $('#new_form_trigger').on('click',function(){
            if(!$(this).find('input').is(':checked')) {
                $("#existing_form").fadeOut(function () {
                    $("#new_form").fadeIn();

                });
            }
        });

        $('#existing_extractions').on('change', function(val, added, removed){
            $("#extraction_chooser").attr('action', App.routing.generate('extraction_filtering', {extraction_id : val.val}));
        });

        $("#extraction_chooser").submit(function(e){
            var extraction_id = $('#existing_extractions').select2('val');
            if(extraction_id == ''){
                e.preventDefault();
            }
        });
    });
}
)(jQuery, ListBroking)