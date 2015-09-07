/**
 * Created by scastro on 11/10/14.
 */

(function ($, App) {
    $(function () {
        "use strict";

        // Init collections
        $('[data-collection]').each(function () {

            var $collectionHolder = $(this);
            var $collections = $collectionHolder.find('.collection');

            // If no widget was added add one
            var index = $collections.length;
            $collectionHolder.data('index', index);
            if (index == 0) {
                addCollection($collectionHolder);
            }else{

                addControls($collectionHolder);
            }
        });
        initDateRangePickers($('form'));

        /**
         * Adds a new collection
         * @param $collectionHolder
         */
        function addCollection($collectionHolder) {

            var prototype = $collectionHolder.data('prototype');

            var index = $collectionHolder.data('index') + 1;
            var $newForm = $(prototype.replace(/__name__/g, index));

            // Add the new index to the collectionHolder and form
            $collectionHolder.data('index', index);
            $collectionHolder.append($newForm);

            // Select2 Ajax widgets
            $collectionHolder.find("[data-select-mode=local]").select2();
            $("[data-mask]").inputmask();

            initDateRangePickers($collectionHolder);
            addControls($collectionHolder);
        }

        /**
         * Removes a collection
         * @param $collectionHolder
         * @param $collection
         */
        function removeCollection($collectionHolder, $collection) {

            $collection.remove();
            var new_index = 0;
            if ($collectionHolder.find('.collection').length > 0) {

                new_index = $collectionHolder.data('index') - 1;
            }
            $collectionHolder.data('index', new_index);

            addControls($collectionHolder);
        }

        /**
         * Adds the add/remove controls
         * @param $collectionHolder
         */
        function addControls($collectionHolder){

            var collectionControl = '<div class="collection_control btn-group" style="margin: 0 0 -5px 5px;"><button type="button" class="btn btn-xs btn-danger del_collection"><i class="fa fa-minus"></i></button><button type="button" class="btn btn-xs btn-default add_collection"><i class="fa fa-plus"></i></button></div>';
            var collectionDelete = '<div class="collection_control btn-group" style="margin: 0 0 0 5px;"><button type="button" class="btn btn-xs btn-danger del_collection"><i class="fa fa-minus"></i></button></div>';
            var collectionAdd = '<div class="collection_control btn-group" style="margin: 0 0 0 5px;"><button type="button" class="btn btn-xs btn-default add_collection"><i class="fa fa-plus"></i></button></div>';

            var $collections = $collectionHolder.find('.collection');

            // remove old controls
            $collectionHolder.find('.collection_control').remove();

            // Only one widget
            if($collections.length == 1){
                $collections.find('label:first').after(collectionAdd);
            }else{
                // Dont apply to the first and the last collections
                $collectionHolder.find('.collection:not(:last-child).collection:not(:first-child)').find('label:first').after(collectionDelete);

                // Only apply to the last collection
                $collectionHolder.find('.collection:last label:first').after(collectionControl);
            }

            refreshClickEvents($collectionHolder);
        }

        /**
         * Refreshes the controls eventListeners
         * @param $collectionHolder
         */
        function refreshClickEvents($collectionHolder){

            // Remove old events
            $collectionHolder.find('.collection_control').undelegate('click');

            $collectionHolder.find('.add_collection').on('click', function (e) {
                e.preventDefault();

                // add a new collection
                addCollection($collectionHolder);
            });

            $collectionHolder.find('.del_collection').on('click', function (e) {
                e.preventDefault();

                // Remove a collection
                removeCollection($collectionHolder, $(this).parents('.collection'));
            });
        }

        /**
         * Starts the DateRange Pickers
         * @param $this
         */
        function initDateRangePickers($this) {
            // Datepickers
            $this.find('[data-toggle=birthdaterangepicker]').each(function(){
                if($(this).siblings('.help-block').length <= 0){
                    $(this).after('<div class="help-block"></div>');
                }
                $(this).daterangepicker({
                        format: 'YYYY/MM/DD',
                        ranges: {
                            '18-24 Years': [moment().subtract(24, 'years'), moment().subtract(18, 'years')],
                            '25-34 Years': [moment().subtract(34, 'years'), moment().subtract(25, 'years')],
                            '35-44 Years': [moment().subtract(44, 'years'), moment().subtract(35, 'years')],
                            '45-54 Years': [moment().subtract(54, 'years'), moment().subtract(45, 'years')],
                            '55-64 Years': [moment().subtract(64, 'years'), moment().subtract(55, 'years')],
                            '65-90 Years': [moment().subtract(90, 'years'), moment().subtract(65, 'years')]
                        }
                    },
                    function (start, end) {

                        var $datepicker = $(this.element);
                        var $help = $datepicker.next('.help-block');

                        $help.html('Range: ' + $datepicker.data('daterangepicker').chosenLabel);
                    });

                $(this).next('.help-block').html('Range: ' + $(this).data('daterangepicker').chosenLabel);
            });


            // Datepickers
            $this.find('[data-toggle=daterangepicker]').each(function(){
                if($(this).siblings('.help-block').length <= 0){
                    $(this).after('<div class="help-block"></div>');
                }
                $(this).daterangepicker({
                        format: 'YYYY/MM/DD',
                        ranges: {
                            'Last 3 years': [moment().subtract(3, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                            'Last 2 years': [moment().subtract(2, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                            'Last year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                            'Last 6 months': [moment().subtract(6, 'months'), moment().endOf('month')],
                            'Last 3 months': [moment().subtract(3, 'months'), moment().endOf('month')],
                            'Last Month': [moment().subtract(1, 'months'), moment().subtract(1,'months').endOf('month')],
                            'This Month': [moment().startOf('month'), moment().endOf('month')]
                        }
                    },
                    function (start, end) {

                        var $datepicker = $(this.element);
                        var $help = $datepicker.next('.help-block');

                        $help.html('Range: ' + $datepicker.data('daterangepicker').chosenLabel);
                    });

                $(this).next('.help-block').html('Range: ' + $(this).data('daterangepicker').chosenLabel);
            });
        }
    });
})(jQuery, ListBroking);