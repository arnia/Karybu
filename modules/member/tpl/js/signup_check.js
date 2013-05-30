/**
 * @brief Register at Sinai details of each item, and the ability to implement redundancy check
 * @author Arnia (dev@karybu.org)
 **/

// Started as input variables for ajax to the server to check if the content was changed for a fixed amount of time after entering
var memberCheckObj = { target:null, value:null }

// domready Putting down the events for a particular field
jQuery(document).ready(memberSetEvent);

function memberSetEvent() {
	jQuery('#fo_insert_member :input')
		.filter('[name=user_id],[name=nick_name],[name=email_address]')
		.blur(memberCheckValue);
}


// Physical server to request a specific field value check if the message sprinkle over the function
function memberCheckValue(event) {
	var field  = event.target;
	var _name  = field.name;
	var _value = field.value;
	if(!_name || !_value) return;

	var params = {name:_name, value:_value};
	var response_tags = ['error','message'];

	exec_xml('member','procMemberCheckValue', params, completeMemberCheckValue, response_tags, field);
}

// The response from the server if you have more than two when it comes to output messages
function completeMemberCheckValue(ret_obj, response_tags, field) {
	var _id   = 'dummy_check'+field.name;
	var dummy = jQuery('#'+_id);
   
    if(ret_obj['message']=='success') {
        dummy.html('').hide();
        return;
    }

	if (!dummy.length) {
		dummy = jQuery('<div class="checkValue" />').attr('id', _id).appendTo(field.parentNode);
	}

	dummy.html(ret_obj['message']).show();
}

// Function to clean up the resulting message
function removeMemberCheckValueOutput(dummy, obj) {
    dummy.style.display = "none";
}
