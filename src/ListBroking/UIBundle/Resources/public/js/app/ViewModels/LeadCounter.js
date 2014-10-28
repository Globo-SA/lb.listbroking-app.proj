/**
 * Created by scastro on 19/10/14.
 */
define(['ko'], function(ko){
    function LeadCounter(){
        this.open = ko.observable('');
        this.lock = ko.observable('');
        this.total = ko.dependentObservable(function(){
            return this.open() + this.lock();
        }, this);
    }

    return LeadCounter;
});