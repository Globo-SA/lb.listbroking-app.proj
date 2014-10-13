/**
 * Created by scastro on 11/10/14.
 */

(function ($, App)
{
    $(function() {
        "use strict";

        // Select2 Ajax widgets
        $("[data-select=ajax]").each(function(){
            var $select = $(this);
            var type = $select.attr('name');
            var $child = $($select.data('child'));
            if($child.length > 0){
                $child.data('parent', "#" + $select.attr('id'));
            }
            var parent = $select.data('parent') != '' ? $($select.data('parent')).attr('name') : '';

            $select.select2({
                placeholder: $select.data('placeholder'),
                minimumInputLength: 0,
                ajax: {
                    url: App.routing.generate('ajax_lists'),
                    dataType: 'json',
                    data: function (term, page) {
                        return {
                            type: type,
                            parent: parent,
                            parent_id: $select.data('parent_id')
                        };
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
                    // Grab the child using the parent
                    var $child = $($(this.element[0]).data('child'));
                    if($child.length > 0){
                        // Save the parent id on the child
                        $child.data('parent_id', item.id);
                    }
                    return item.name
                }
            });
        });
    });
}
)(jQuery, ListBroking)