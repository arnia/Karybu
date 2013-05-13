/**
 * @brief 회원 가입시나 정보 수정시 각 항목의 중복 검사등을 하는 기능을 구현
 * @author Arnia (developer@xpressengine.com)
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


// 실제 서버에 특정 필드의 value check를 요청하고 이상이 있으면 메세지를 뿌려주는 함수
function memberCheckValue(event) {
	var field  = event.target;
	var _name  = field.name;
	var _value = field.value;
	if(!_name || !_value) return;

	var params = {name:_name, value:_value};
	var response_tags = ['error','message'];

	exec_xml('member','procMemberCheckValue', params, completeMemberCheckValue, response_tags, field);
}

// 서버에서 응답이 올 경우 이상이 있으면 메세지를 출력
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

// 결과 메세지를 정리하는 함수
function removeMemberCheckValueOutput(dummy, obj) {
    dummy.style.display = "none";
}
