/**
 * Created by scastro on 11/10/14.
 */

(function ($, App)
{
    $(function() {
        "use strict";

        $('[data-toggle=simple_table]').dataTable({
            bPaginate: false,
            bFilter: false,
            bSort: true,
            bInfo: false
        });

        $('body').attachExclude();
    });

    $.fn.attachExclude = function(){
        $('.exclude_lead').on('click',function(){
            $(this)
                .toggleClass('btn-warning')
                .toggleClass('btn-default')
                .next('button').slide('left', 'slow');
        });

        $('.confirm_exclude_lead').on('click',function(){
            $('#loading_widget').fadeIn();

            var $btn = $(this);
            $.ajax({
                type: "GET",
                url: App.routing.generate('ajax_extraction_exclude_lead', { extraction_id: $btn.data('extraction-id'), lead_id : $btn.data('lead-id') }),
                dataType: 'json',
                success: function(data){

                    var response = data.response;

                    var $row =  $('.confirm_exclude_lead[data-extraction-id=' + response.extraction_id + '][data-lead-id=' + response.lead_id + ']').parents('tr');
                    $row.fadeOut('slow',function(){
                        var $table = $(this).parents('table');
                        $table.DataTable().row($(this)).remove().draw();
                    });

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
        });
    };

    $.fn.refreshContacts = function(){
        // Add the rendered Form
        $('#extraction_table_container').find('div, input, select').css('background-color', 'rgb(234, 234, 234)');
        $('#extraction_table_container i.icon-huge').removeClass('hidden')
        ;
        $.ajax({
            type: "GET",
            url: App.routing.generate('ajax_extraction_contacts', { extraction_id: App.variables.extractionId }),
            dataType: 'html',
            success: function(data){

                // Add the rendered Form
                $('#extraction_table_container').find('div, input, select').css('background-color', 'rgb(234, 234, 234)');
                $('#extraction_table_container i.icon-huge').addClass('hidden');
                $('#extraction_table_container span')
                    .html(data)
                    .find('table')
                    .dataTable({
                        bPaginate: true,
                        bSort: true,
                        bInfo: true
                    });

                $('body').attachExclude();

                // Loading Widget, stop when everything is loaded
                $('#loading_widget').fadeOut();
            }
        });
    };
}
)(jQuery, ListBroking);