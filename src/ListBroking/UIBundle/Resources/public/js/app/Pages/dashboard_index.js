/**
 * Created by scastro on 19/10/14.
 */
require(['ko', 'jquery', '../viewModels/LeadCounter'], function (ko, $, LeadCounter) {

    // Bing the viewModel
    var counter = new LeadCounter();
    ko.applyBindings(counter);

    // Grab Leads/Locks using ajax
    function getLeadsCount(counter){
        $.getJSON(Routing.generate('ajax_count_leads'), function(data) {
            data = data.response;
            counter.open(data.open_leads);
            counter.lock(data.lock_leads);
        });
    }

    // Check for new Leads/Locks every 10s
    getLeadsCount(counter);
    window.setInterval(function(){
        getLeadsCount(counter);
    }, 10000);

});