/**
 * Created by scastro on 11/10/14.
 */

(function ($, App)
{
    $(function() {
        "use strict";

        $('form[data-form-type=ajax]').submit(function(e){
            e.preventDefault();

            var $form = $(this);
            var form_name = $form.attr('name');
            var form_data = $form.serializeArray();

            var $submit_btn = $form.find("button[type=submit]");
            $submit_btn.data('old_html', $submit_btn.html());
            $submit_btn.html('<i class="icon icon-large ion-loading-c"></i>Saving...');

            $.ajax({
                type: "POST",
                url: App.routing.generate('ajax_form_submit', { form_name: form_name }),
                dataType: 'json',
                data: form_data,
                success: function(data){

                    var response = data.response;

                    // Get the form and remove errors and old values
                    $form = $("[name=" + response.form_name + "]");
                    $form.find('.form-group')
                        .removeClass('has-error')
                    ;
                    $form.find('.help-block')
                         .text(null)
                    ;
                    $form.find('.global-errors')
                         .text(null)
                    ;
                    $form.find('select,input,textarea')
                         .val(null)
                    ;
                    $form.find('[type=checkbox]')
                         .iCheck('uncheck')
                    $form.find('[type=hidden][data-select-mode]')
                         .select2("val", null)
                    ;

                    // Refresh the csrf_token
                    $form.find("#" + response.form_name + "__token").val(response.new_csrf);

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

                    // Get the form and remove errors and old values
                    $form = $("[name=" + response.form_name + "]");

                    // Refresh the csrf_token
                    $form.find("#" + response.form_name + "__token").val(response.new_csrf);

                    $form.find('.form-group')
                        .removeClass('has-error')
                    ;
                    $form.find('.help-block')
                        .text('')
                    ;
                    $form.find('.global-errors')
                        .text(null)
                    ;

                    var errors = response.errors;
                    for (var key in errors) {
                        if(!isNaN(parseInt(key))){
                               $form.append('<p class="text-danger global-errors">' + errors[key] + '</p>');
                        }else{
                            if (errors.hasOwnProperty(key)) {
                                $.each(errors[key], function(field_name, error){
                                    var $field = $('#' + key + "_" + field_name);
                                    $field.parents('.form-group')
                                        .addClass('has-error').
                                        find('.help-block')
                                        .text(error);
                                 });
                            }
                        }
                    };

                    // Remove loading from the sumbmit button
                    var $submit_btn = $form.find("button[type=submit]");
                    $submit_btn.html($submit_btn.data('old_html'));

                    // Loading Widget, stop when everything is loaded
                    $('#loading_widget').fadeOut();
                }
            });
        });

        var $list = $('#email_list');
        var emailCount = $list.data('initial-count');
        $('#add-another-email').click(function(){

            var newWidget = $list.data('prototype');
            newWidget = newWidget.replace(/__name__/g , emailCount);
            emailCount++;

            // create a new list element and add it to the list
            var newLi = $('<li></li>').html(newWidget);

            var $removeFormA = $('<a href="#">delete this tag</a>');
            $removeFormA.on('click', function(e) {
                // prevent the link from creating a "#" on the URL
                e.preventDefault();

                // remove the li for the tag form
                newLi.remove();
            });
            newLi.append($removeFormA);
            newLi.appendTo($list);

        });
    });
}
)(jQuery, ListBroking)