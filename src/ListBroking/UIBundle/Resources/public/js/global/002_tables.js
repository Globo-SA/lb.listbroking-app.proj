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
            bPaginate: true,
            bLengthChange: true,
            bSort: true,
            bInfo: true,
            "scrollX": true
        });

        $('.exclude_lead').on('click',function(){
            $(this)
                .toggleClass('btn-warning')
                .toggleClass('btn-default')
                .next('button').slide('left', 'slow');
        })

        $('.confirm_exclude_lead').on('click',function(){
            $('#loading_widget').fadeIn();

            var $btn = $(this);
            $.ajax({
                type: "GET",
                url: App.routing.generate('ajax_extraction_exclude_lead', { extraction_id: $btn.data('extraction-id'), lead_id : $btn.data('lead-id') }),
                dataType: 'json',
                success: function(data){

                    var response = data.response;

                    $('.confirm_exclude_lead[data-extraction-id=' + response.extraction_id + '][data-lead-id=' + response.lead_id + ']').parents('tr').fadeOut('slow',function(){
                        $(this).remove();
                    })
                    var val = $('#filters_lead_details_lead\\:id').select2('val');
                    val.push(response.lead_id);

                    $('#filters_lead_details_lead\\:id').select2('val', val);

                    // Loading Widget, stop when everything is loaded
                    $('#loading_widget').fadeOut();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var response = jqXHR.responseJSON.response;
                    console.log(response);

                    // Loading Widget, stop when everything is loaded
                    $('#loading_widget').fadeOut();
                }
            });
        })
    });
}
)(jQuery, ListBroking)