function completeReload(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    location.reload();
}

function langSelect(){
    var lang = document.getElementById("languages").value;
    doChangeLangType(lang);
}

