/**
 * Created by scastro on 11/10/14.
 */

(function ($, App)
{
    $(function() {
        "use strict";

        $('[data-toggle=simple_table]').on('init.dt', function () {
            $('select[name=leads_table_length]').select2();
        }).dataTable({
            "bPaginate": true,
            "bLengthChange": true,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": false
        });

        $('.exclude_lead').on('click',function(){
            $(this).next('button').slide('left', 'slow');
        })
    });
}
)(jQuery, ListBroking)