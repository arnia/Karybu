function showErrorMessage() {
    jQuery('#signinmodal .alert').show();
}

function hideErrorMessage() {
    jQuery('#signinmodal .alert').hide();
}

function setMessageBody(message) {
    jQuery('#signinmodal .alert-body ').html(message);
}

function loginCallback(data) {
    if (data.login_message == 'success') {
        location.reload();
        hideErrorMessage();
        return;
    }
    if (data.login_message) {
        setMessageBody(data.login_message);
        showErrorMessage();
    }
}

function callLogin() {
    var userId = jQuery('#inputEmail').val();
    var password = jQuery('#inputPassword').val();
    var keep = $('#keep_signed').attr('checked') ? 'Y' : 'N';
    jQuery.exec_json('member.procMemeberLoginAjax', {'user_id': userId,'password': password,'keep_signed': keep}, loginCallback);
}
