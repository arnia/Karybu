jQuery(function () {
    jQuery('#billing_address, #shipping_address')
        .change(function () {
            var $this = jQuery(this);
            var selected = $this.children(":selected");
            var newAddress = ($this.is('#billing_address')) ? jQuery('#new-address-billing') : jQuery('#new-address-shipping');
            if (selected.val() == 'new') newAddress.show(200); else newAddress.hide(200);
        }).filter('#billing_address').change();
    jQuery('#different_shipping').change(function(){
        var $this = jQuery(this);
        if (jQuery(this).is(':checked')) {
            var shippingSelect = jQuery('#shipping-address-select').show();
            if (shippingSelect.find('option:selected').val() == 'new') {
                jQuery('#new-address-shipping').show();
            }
        }
        else {
            jQuery('#new-address-shipping, #shipping-address-select').hide();
        }
    }).change();

    //validations

    jQuery('#login_form').validate({
        rules: {
            'login[user]': { required: true, email: true },
            'login[pass]': "required"
        },
        messages: {
            'login[user]': { required: "Please specify your username", email: "Invalid email format for username" },
            'login[pass]': { required: "Please specify your password" }
        }
    });
    jQuery('#big').validate({ //http://docs.jquery.com/Plugins/Validation/validate#options
        rules: {
            'new_billing_address[firstname]': { required: true },
            'new_billing_address[lastname]': { required: true },
            'new_billing_address[email]': { required: true, email: true },
            'new_billing_address[telephone]': { required: true, phone: true },
            'new_billing_address[fax]': { phone: true },
            'new_billing_address[address]': { required: true, minlength: 10 },

            'new_shipping_address[firstname]': { required: '#different_shipping:checked' },
            'new_shipping_address[lastname]': { required: '#different_shipping:checked' },
            'new_shipping_address[email]': { required: '#different_shipping:checked', email: true },
            'new_shipping_address[telephone]': { required: '#different_shipping:checked', phone: true },
            /*'new_shipping_address[fax]': { required: '#different_shipping:checked', phone: true },*/
            'new_shipping_address[address]': { required: '#different_shipping:checked', minlength: 10 },
            'new_shipping_address[postal_code]': { required: '#different_shipping:checked' },
            'new_shipping_address[country]': { required: '#different_shipping:checked' }
        },
        messages: {
            'new_billing_address[firstname]': { required: 'First name is mandatory' },
            'new_billing_address[lastname]': { required: 'Please enter last name' },
            'new_billing_address[email]': { required: 'Email contact for shipping address?', email: 'Wrong format for email' },
            'new_billing_address[telephone]': { required: 'Please provide the phone number', phone: 'Please enter a valid phone number for the billing address' },
            'new_billing_address[fax]': { phone: 'Please provide a valid fax number' },
            'new_billing_address[address]': { required: 'Forgot about address?', minlength: jQuery.format("At least {0} characters required for billing address!") },
            'new_shipping_address[firstname]': { required: 'First name is mandatory' },
            'new_shipping_address[lastname]': { required: 'Please enter last name' },
            'new_shipping_address[email]': { required: 'Email contact for shipping address?', email: 'Wrong format for email' },
            'new_shipping_address[telephone]': { required: 'Please provide the phone number', phone: 'Please enter a valid phone number for shipping' },
            'new_shipping_address[fax]': { phone: 'Please enter a valid fax number for shipping' },
            'new_shipping_address[address]': { required: 'Forgot about the shipping address?', minlength: jQuery.format("At least {0} characters required for shipping address!") }
        }
    });
    jQuery.validator.addMethod("phone", function(phone_number, element) {
        phone_number = phone_number.replace(/\s+/g, "");
        return this.optional(element) || phone_number.length > 5 &&
            phone_number.match(/^[)\(0-9 -+]*$/);
        //for US: phone_number.match(/^(1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
    }, "Please specify a valid phone number");
});