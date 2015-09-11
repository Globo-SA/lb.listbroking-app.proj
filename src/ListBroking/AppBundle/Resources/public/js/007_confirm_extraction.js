/**
 * Created by scastro on 09/12/14.
 */
(function ($, App) {
    $(function () {
        "use strict";

        var $confirm_btn = $("#confirm_extraction_btn");
        var $confirm_chk = $("#confirm_extraction_chk");
        var $finalize_btn = $("#finalize_extraction_btn");

        var $checkboxes = $('[id^=extraction_locking_lock_type]');

        // Confirm that the Extraction is final before submit
        $confirm_chk.on('ifChanged', function () {
            if($confirm_btn.is(':disabled')){
                $confirm_btn.removeAttr('disabled');
            }else{
                $confirm_btn.attr('disabled', 'disabled');
            }
        });

        // Check if there is at least 1 lock_type selected for
        // enabling the finalize button
        $checkboxes.on('change', function () {
            if ($checkboxes.is(':checked')) {
                $finalize_btn.removeClass('disabled');
                $finalize_btn.removeAttr('disabled');
            } else {
                $finalize_btn.addClass('disabled');
                $finalize_btn.attr('disabled', 'disabled');
            }
        });

        // Generate the locks and redirect to the last step
        $finalize_btn.click(function () {

            $(this).find('i.ion-loading-c').fadeIn();

            // Map all selected lock types
            var lock_types = $checkboxes.filter(':checked').map(function () {
                return parseInt($(this).val());
            }).get();

            if (lock_types.length == 0 && !confirm('Do you really want to continue without locking the leads ?')) {
                return false;
            }

            // Send locks to the server
            $.ajax({
                type: "POST",
                url: App.routing.generate('ajax_extraction_locks', {extraction_id: App.variables.extractionId}),
                dataType: 'json',
                data: {lock_types: lock_types},
                success: function (data) {

                    window.location = $finalize_btn.data('url');
                }
            });
        });
    });
})(jQuery, ListBroking);