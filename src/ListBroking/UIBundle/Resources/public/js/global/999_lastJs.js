/**
 * Created by scastro on 11/10/14.
 */

// This should always be the last
// script to run
(function ($, App)
{
    $(function() {
        "use strict";

        // Opens all the collapsed boxes
        $(".box.collapsed-box").each(function() {
            var box = $(this);
            var bf = box.find(".box-body, .box-footer");

            box.removeClass("collapsed-box");
            bf.slideDown();
        });

        // Loading Widget, stop when everything is loaded
        $('#loading_widget').fadeOut();

        // Starts the Loading Widget when clicked
        $('[data-load=start],[type=submit]').on('click', function(){
            $('#loading_widget').fadeIn();
        });
    });
}
)(jQuery, ListBroking)