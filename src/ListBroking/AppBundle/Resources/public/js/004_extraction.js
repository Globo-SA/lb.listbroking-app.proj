/**
 * Created by scastro on 11/10/14.
 */

(function ($, App) {
    $(function () {
        "use strict";

        var extraction_logs = [];
        var $panel = $('#extraction_log_panel');
        var $list = $('#extraction_log_list');
        var row = $list.data('prototype');

        if (App.variables.extractionId) {
            App.variables.previousExtractionStatus = App.variables.extractionStatus;

            // Extraction Log Actions
            setInterval(function () {
                $('#loading_widget').fadeIn();
                $.ajax({
                    type: "GET",
                    url: App.routing.generate('ajax_lastest_extraction_log', {extraction_id: App.variables.extractionId}),
                    dataType: 'json',
                    success: function (data) {
                        var logs = data.response;

                        // Only update if there's a new ID
                        if (extraction_logs.length <= 0 || logs[0]['id'] != extraction_logs[0]['id']) {

                            extraction_logs = [];
                            $list.empty();
                            $.each(logs, function (index, value) {

                                // Add new row
                                extraction_logs.push(value);

                                $list.append($.replaceVariables(row, {
                                    id: value['id'],
                                    log: value['log'],
                                    created_at: value['created_at'].date.replace('.000000', '')
                                }));
                            });

                            $panel.addClass('active');
                            setTimeout(function(){
                                $panel.removeClass('active');
                            },5000);
                        }


                        // Loading Widget, stop when everything is loaded
                        $('#loading_widget').fadeOut();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        var response = jqXHR.responseJSON;
                        console.log(response);

                        // Loading Widget, stop when everything is loaded
                        $('#loading_widget').fadeOut();
                    }
                });

            }, App.variables.intervalTimeout);
        }

        if (App.variables.extractionStatus >= 2) {
            $.listenToExtractionChanges(function (extraction) {
                console.log('new extraction:' + extraction);

                // If status changed refresh the page
                if (App.variables.extractionStatus != App.variables.previousExtractionStatus) {
                    window.location.href = window.location.href;

                    return;
                }

                if ($.isExtractionReady(extraction)) {
                    console.log('Extraction is ready!!!');
                    $.refreshSummaryTab(extraction);
                }

                // Change the only "user editable" extraction parameter
                $("#extraction_info_payout").text(extraction.payout);
            });
        }
    });

    /**
     * Listen for Extraction Changes on the backend
     * @param callback
     */
    $.listenToExtractionChanges = function (callback) {

        var previous_extraction = {};

        function runLoop() {
            $.findExtraction(function (new_extraction) {
                if (JSON.stringify(new_extraction) != JSON.stringify(previous_extraction)) {
                    previous_extraction = new_extraction;
                    callback(new_extraction);
                }
            });
        }

        // Run once without timeOut
        runLoop();

        // Run every x seconds
        App.variables.extractionIntervalId = setInterval(function () {
            runLoop();
        }, App.variables.intervalTimeout);
    };

    /**
     * Finds an Extraction using Ajax
     * @param callback
     */
    $.findExtraction = function (callback) {
        $.ajax({
            type: "GET",
            url: App.routing.generate('ajax_find_extraction', {extraction_id: App.variables.extractionId}),
            success: function (data) {
                callback(data.response.response);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr);
            }
        });
    };

    /**
     * Checks if the Extraction is being "worked on" on the backend
     * @param extraction
     * @returns {boolean}
     */
    $.isExtractionReady = function (extraction) {
        return !(!extraction.is_already_extracted || extraction.is_deduplicating || extraction.is_locking);
    };

    /**
     * Refreshes all the partials of the Summary Tab
     * Uses callbacks to start the next refresh
     * @param extraction
     */
    $.refreshSummaryTab = function (extraction) {
        // Refresh SQL Modal
        $.refreshSQLModal(extraction, function () {
            // Refresh Extraction Summary
            $.refreshExtractionSummary(extraction, function () {
                // Refresh Contacts Preview
                $.refreshContactsPreview(extraction, function () {
                    // Enable the DeDuplication Button
                    $.refreshDeduplicationButton(extraction, function () {
                        // Enable the Footer Buttons
                        $.refreshFooterButtons(extraction, function () {
                            // Stop the loading widget
                            $('#loading_widget').fadeOut();
                        });
                    });
                });
            });
        });
    };

    /**
     * Refreshes the SQL partial of the Summary Tab
     * @param extraction
     * @param callback
     */
    $.refreshSQLModal = function (extraction, callback) {
        var $modal = $('#sql-modal');

        $modal.find('.modal-body .dql').html(extraction.query.dql);

        callback();
    };

    /**
     * Refreshes the Extraction Summary  partial of the Summary Tab
     * @param extraction
     * @param callback
     */
    $.refreshExtractionSummary = function (extraction, callback) {
        var $summary_tab = $('#extraction_summary');
        $.ajax({
            type: "GET",
            url: App.routing.generate('ajax_extraction_summary', {extraction_id: App.variables.extractionId}),
            dataType: 'html',
            success: function (data) {
                $summary_tab.html(data);

                // Remove Loading styles
                $summary_tab.removeClass('grey-bg');
                $summary_tab.find('i.icon-small').addClass('hidden');

                callback();
            }
        });
    };

    /**
     * Refreshes the Contacts Preview partial of the Summary Tab
     * @param extraction
     * @param callback
     */
    $.refreshContactsPreview = function (extraction, callback) {
        var $preview_table = $('#extraction_table_container');

        $.ajax({
            type: "GET",
            url: App.routing.generate('ajax_extraction_contacts', {extraction_id: App.variables.extractionId}),
            dataType: 'html',
            success: function (data) {
                $preview_table.html(data);

                // Remove Loading styles
                $preview_table.removeClass('grey-bg');
                $preview_table.find('i.icon-huge').addClass('hidden');

                callback();
            }
        });
    };

    /**
     * Refreshes the Deduplication button (is_deduplicating or not and disable/enable)
     * @param extraction
     * @param callback
     */
    $.refreshDeduplicationButton = function (extraction, callback) {

        $('#lead_deduplication_trigger')
            .removeAttr('disabled')
            .find('i.loading').fadeOut();

        callback();
    };

    /**
     * Enable all the footer buttons
     * @param extraction
     * @param callback
     */
    $.refreshFooterButtons = function (extraction, callback) {

        $('.footer-button:not(#confirm_extraction_btn)').removeAttr('disabled');
        $('.footer-button[type=checkbox]').iCheck('enable').iCheck('update');
        if (extraction.is_delivering) {
            $('#deliver_extraction_trigger, #deduplication_download')
                .attr('disabled', 'disabled')
                .find('i.loading, i.ion-loading-c').fadeIn();
        } else {
            $('#deliver_extraction_trigger, #deduplication_download')
                .removeAttr('disabled')
                .find('i.loading, i.ion-loading-c').fadeOut();
        }
        callback();
    };

    $.replaceVariables = function (row, variables) {
        $.each(variables, function (k, v) {
            row = row.replace('%%' + k + '%%', v);
        });

        return row;
    }
})(jQuery, ListBroking);