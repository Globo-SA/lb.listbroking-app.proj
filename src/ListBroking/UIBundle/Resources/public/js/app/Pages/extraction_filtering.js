/**
 * Created by scastro on 20/10/14.
 */
require(['ko', 'jquery', 'select2'], function (ko, $, select2) {

    ko.components.register('filter-widget', {
        viewModel: function(params) {

            this.label = params.label;
            this.name = params.name;
            this.type = params.type;
            this.value = params.value;

            if(this.type == 'select2'){
                this.type = 'text';
                $(this).select2();
            }

            // Behaviors
            //this.like = function() { this.chosenValue('like'); }.bind(this);
            //this.dislike = function() { this.chosenValue('dislike'); }.bind(this);
        },
        template: { require : 'text!templates/filters.html' }
    });

    function Filter(label, name, type, value, data) {
        this.label = label;
        this.name = name;
        this.type = type;
        this.value = value;
        this.data = data;
    }

    function FiltersViewModel() {
        this.filters = [{
            label: 'Contact Details',
            fields: [
                new Filter('Gender', 'contact_gender', 'select2', 'M'),
                new Filter('Birthdate', 'contact_birthdate_range', 'text', '2014-01-01'),
                new Filter('Email', 'contact_email', 'text', '')
            ]
        }];
    }

    ko.applyBindings(new FiltersViewModel());
});

