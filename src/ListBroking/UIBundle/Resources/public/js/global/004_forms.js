/**
 * Created by scastro on 11/10/14.
 */

(function ($, App)
{
    $(function() {
        "use strict";

        $('form').submit(function(e){
            e.preventDefault();

            var $form = $(this);
            var form_name = $form.attr('name');
            var form_data = $form.serializeArray();

            var $submit_btn = $form.find("button[type=submit]");
            $submit_btn.data('old_html', $submit_btn.html());
            $submit_btn.html('<i class="icon icon-large ion-loading-c"></i>Saving...');

            $.ajax({
                type: "POST",
                url: App.routing.generate('ajax_form_submit', { name: form_name }),
                dataType: 'json',
                data: form_data,
                success: function(data){

                    var response = data.response;

                    // Get the form and remove errors and old values
                    $form = $("[name=" + response.name + "]");
                    console.log($form.find("#" + response.name + "__token").val());
                    $form.find('.form-group')
                        .removeClass('has-error')
                    ;
                    $form.find('.help-block')
                        .text('')
                    ;
                    $form.find('select,input,textarea')
                        .val(null)
                        .iCheck('uncheck')
                    ;

                    // Refresh the csrf_token
                    $form.find("#" + response.name + "__token").val(response.new_csrf);
                    console.log($form.find("#" + response.name + "__token").val());

                    // Show success msg with edit link
                    var edit_link =
                            '&nbsp;&nbsp;<a href="#" class="btn btn-link btn-xs">' +
                            '<i class="fa fa-pencil-square-o"></i>' +
                            'Edit' +
                            '</a>'
                        ;
                    $form.parents('.box:first').find('.alert strong')
                        .html(data.response.msg + edit_link)
                        .parents('.alert')
                        .fadeIn()
                    ;
                    setTimeout(function(){
                        $form.parents('.box:first').find('.alert strong')
                            .html(null)
                            .parents('.alert')
                            .fadeOut()
                    },10000);

                    // Remove loading from the sumbmit button
                    var $submit_btn = $form.find("button[type=submit]");
                    $submit_btn.html($submit_btn.data('old_html'));

                    // Loading Widget, stop when everything is loaded
                    $('#loading_widget').fadeOut();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var response = jqXHR.responseJSON.response;
                    $.each(response, function(form_name, fields){
                        $.each(fields, function(field_name, error){
                            var $field = $('#' + form_name + "_" + field_name);
                            var $form_group = $field.parents('.form-group').addClass('has-error');
                            $form_group.find('.help-block').text(error);
                        });
                    });

                }
            });
        })
    });
}
)(jQuery, ListBroking)