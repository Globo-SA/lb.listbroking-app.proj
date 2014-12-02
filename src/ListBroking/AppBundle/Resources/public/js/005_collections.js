/**
 * Created by scastro on 11/10/14.
 */

(function ($, App)
{
    $(function() {
        "use strict";

       $('[data-collection]').each(function(){

           var $collectionHolder = $(this);

           var index = $collectionHolder.find('[data-id]').length;
           $collectionHolder.data('index', index);
           if(index == 0){
               addTagForm($collectionHolder);
           }

           $collectionHolder.find('.add_collection').on('click', function(e) {
               e.preventDefault();

               // add a new tag form (see next code block)
               addTagForm($collectionHolder);
           });

           $collectionHolder.find('.del_collection').on('click', function(e) {
               e.preventDefault();

               var $collectionHolder2 = $(this).parent('div').parent('div')
               $(this).parent('div').remove();

               var new_index = 0;
               if($collectionHolder2.find('div').length > 0){

                   new_index = $collectionHolder2.data('index') - 1;
               }
               $collectionHolder2.data('index', new_index);

           });
       });
        initDateRangePickers($('form'));

        function addTagForm($collectionHolder) {
            var prototype = $collectionHolder.data('prototype');

            var index = $collectionHolder.data('index') + 1;

            var $newForm = $(prototype.replace(/__name__/g, index));

            $newForm.find('.del_collection').on('click', function(e) {
                e.preventDefault();
                $(this).parent('div').remove();
            });

            $collectionHolder.data('index', index );

            $collectionHolder.find('.add_collection').after($newForm);

            // Select2 Ajax widgets
            $collectionHolder.find("[data-select-mode=local]").select2();
            $("[data-mask]").inputmask();

            initDateRangePickers($collectionHolder);

        }

        function initDateRangePickers($this){
            // Datepickers
            $this.find('[data-toggle=daterangepicker]').daterangepicker({
                    format: 'YYYY/MM/DD',
                    ranges: {
                        '18-24 Years': [moment().subtract(24, 'years'), moment().subtract(18, 'years')],
                        '25-34 Years': [moment().subtract(34, 'years'), moment().subtract(25, 'years')],
                        '35-44 Years': [moment().subtract(44, 'years'), moment().subtract(35, 'years')],
                        '45-54 Years': [moment().subtract(54, 'years'), moment().subtract(45, 'years')],
                        '55-64 Years': [moment().subtract(64, 'years'), moment().subtract(55, 'years')],
                        '65-90 Years': [moment().subtract(90, 'years'), moment().subtract(65, 'years')]
                    },
                    startDate: moment().subtract(29, 'days'),
                    endDate: moment()
                },
                function(start, end) {
                    var range_start = moment().format('YYYY') - end.format('YYYY');
                    var range_end = moment().format('YYYY') - start.format('YYYY');

                    var $datepicker = this.element;
                    var $help = $datepicker.next('.help-block');
                    if($help.length == 0){
                        $datepicker.after('<div class="help-block"></div>');
                        $help = $datepicker.next('.help-block');
                    }
                    $help.html('Range: <strong>' + range_start + '-' + range_end + ' Years</strong>');
                });
        }
    });
}
)(jQuery, ListBroking);