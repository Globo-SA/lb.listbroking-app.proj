/**
 * Created by scastro on 11/10/14.
 */

(function ($, App)
{
    $(function() {
        "use strict";

       $('[data-collection]').each(function(){

           var $collectionHolder = $(this);

           $collectionHolder.data('index', $collectionHolder.find('[data-id]').length);
           addTagForm($collectionHolder);

           $collectionHolder.find('.add_collection').on('click', function(e) {
               e.preventDefault();

               // add a new tag form (see next code block)
               addTagForm($collectionHolder);
           });
       });

        function addTagForm($collectionHolder) {
            var prototype = $collectionHolder.data('prototype');

            var index = $collectionHolder.data('index');

            var $newForm = $(prototype.replace(/__name__/g, index));

            $newForm.find('.del_collection').on('click', function(e) {
                e.preventDefault();
                $('#'+ $(this).data('id')).remove();
            });

            $collectionHolder.data('index', index + 1);

            $collectionHolder.find('.add_collection').before($newForm);

            // Select2 Ajax widgets
            $collectionHolder.find("[data-select-mode=local]").select2();
        }
    });
}
)(jQuery, ListBroking)