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
    });
}
)(jQuery, ListBroking)