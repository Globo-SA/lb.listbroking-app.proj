/**
 * Created by scastro on 11/10/14.
 */

(function ($, App)
{
    $(function() {
        "use strict";

        $('[data-toggle=simple_table]').dataTable({
            "bPaginate": true,
            "bLengthChange": false,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": false
        })
        ;
    });
}
)(jQuery, ListBroking)