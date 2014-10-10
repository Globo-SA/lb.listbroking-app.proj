(function ($, App)
{
    $(function() {
        "use strict";

        $('table').dataTable({
            "bPaginate": true,
            "bLengthChange": false,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": false
        })
        .fadeIn('slow');

        $('select').select2();
    });
}
)(jQuery, ListBroking)