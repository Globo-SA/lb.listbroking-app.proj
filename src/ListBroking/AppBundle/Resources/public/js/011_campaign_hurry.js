(function ($, App) {
    $(function() {
        var form = $('.content form[role="form"]'),
            field_prefix = /[\?&]uniqid=([\w\d]+)($|&)/.exec(form.prop('action'))[1],
            selectedClient,
            accounts = [],
            divClient = form.find('#sonata-ba-field-container-' + field_prefix + '_client'),
            $div_out = $("<div>").addClass("form-group"),
            $div_in = $("<div>").addClass("sonata-ba-field sonata-ba-field-standard-natural"),
            $label = $("<label>").addClass("control-label").text("Account"),
            $selectAccount = $("<select>"),
            inputAccountName = form.find('#sonata-ba-field-container-' + field_prefix + '_account_name').find("input"),
            inputAccountId = form.find('#sonata-ba-field-container-' + field_prefix + '_account_id').find("input");

        function showAccountDropdown() {
            $div_out.append($label).insertAfter(divClient);
            $div_in.append($selectAccount).appendTo($div_out);
            $selectAccount.select2({width:"100%"});
        }

        function hurryAccountRequest()
        {
            $.getJSON('/app.php/admin/ajax/hurry/accounts',{},function(data){
                if (data.code != 200 || !!data.response.error || data.response.accounts.length <= 0)
                {
                    return;
                }
                accounts = data.response.accounts;
                selectedClient = divClient.find("select option[selected]").text();
                if (!selectedClient)
                {
                    selectedClient = divClient.find("select option:first").text();
                }
                populateAccountSelect(accounts, inputAccountId.val());

            });
        }


        function populateAccountSelect(accounts, selectedAcc)
        {
            accounts.forEach(function (el) {
                if (el.client_name.toLowerCase() === selectedClient.toLowerCase()) {
                    $selectAccount.append($("<option>").val(el.id).text(el.name));
                }
            });
            if (!!selectedAcc)
            {
                $selectAccount.select2("val",selectedAcc);
            }
            else
            {
                selectedAcc = $selectAccount.find('option:first').val();
                $selectAccount.select2("val",selectedAcc);
            }
            $selectAccount.trigger("change", selectedAcc);
        }

        divClient.on("change", "select", function (ev) {
            $selectAccount.empty();
            selectedClient = $(ev.target).find("option[value='"+ev.val+"']").text();
            populateAccountSelect(accounts);
        });

        $selectAccount.on("change",function(ev, value) {
            if (typeof ev.val === "undefined")
            {
                ev.val = value;
            }
            inputAccountId.val(ev.val);
            inputAccountName.val($(ev.target).find("option[value='"+ev.val+"']").text());
        });

        showAccountDropdown();
        hurryAccountRequest();
    })
})(jQuery, ListBroking);
