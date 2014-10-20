/**
 * Created by scastro on 14/10/14.
 */

(function ($, App)
{
    $(function() {
        "use strict";

        $('[data-multiple-range]').each(function(){
            var $input = $(this);

            $input.popover({
                placement: 'top',
                title: 'Multiple Range Selector',
                html: true,
                content:
                    '<div class="row">' +
                        '<div class="form-group">' +
                            '<label for="filters_location_postalcode1" class="col-sm-3 control-label">Start:&nbsp;</label>' +
                            '<div class="col-sm-9">' +
                                '<input type="text" class="form-control input-sm">' +
                            '</div>' +
                        '</div>' +
                        '<hr>' +
                        '<div class="form-group">' +
                            '<label for="filters_location_postalcode1" class="col-sm-3 control-label">End:&nbsp;&nbsp;</label>' +
                            '<div class="col-sm-9">' +
                                '<input type="text" class="form-control input-sm">' +
                            '</div>' +
                        '</div>' +
                    '</div>'

            });
        });
    });
}
)(jQuery, ListBroking)