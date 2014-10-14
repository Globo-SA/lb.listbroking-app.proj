/**
 * Created by scastro on 11/10/14.
 */

(function ($, App)
{
    $(function() {
        "use strict";

        // Select2 Ajax widgets
        $("[data-select-mode=local]").select2();
        $("[data-select-mode=ajax]").each(function(){

            // Current data type
            var $select = $(this);

            $select.select2({
                placeholder: $select.data('select-placeholder'),
                minimumInputLength: 0,
                minimumResultsForSearch: -1, //TODO: Fix the ajax search
                allowClear: true,
                ajax: {
                    url: App.routing.generate('ajax_lists'),
                    dataType: 'json',
                    data: function (term, page) {

                        // Ajax call data
                        var data = {};
                        data.type = $select.data('select-type');

                        // Parents data type (if parents exists)
                        var $parent = $($select.data('select-parent'));
                        if($parent.length > 0){
                            data.parent_type = $parent.data('select-type');
                            data.parent_id = $parent.select2('val');
                        }
                        //data.q = term;

                        return data;
                    },
                    results: function (data, page) {
                        return {results: data.response};
                    }
                },
                formatResult: function(item){
                    var markup = "";
                    if (item.name !== undefined) {
                        markup += "<option value='" + item.id + "'>" + item.name + "</option>";
                    }
                    return markup;
                },
                formatSelection: function(item){
                    return item.name
                }
            }).on('change', function(val, added, removed){
                $($select.data('select-child')).select2('val', null);
            }).on('select2-clearing', function(val, added, removed){
                $($select.data('select-child')).select2('val', null);
            });
        });
    });
}
)(jQuery, ListBroking)