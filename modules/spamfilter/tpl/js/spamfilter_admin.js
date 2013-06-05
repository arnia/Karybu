/**
 * @brief Baned IP Delete
 **/
function doDeleteDeniedIP(ipaddress) {
    if (confirm(xe.lang.confirm_delete)){
        var fo_obj = get_by_id('spamfilterDelete');
        fo_obj.ipaddress.value = ipaddress;
        fo_obj.act.value = "procSpamfilterAdminDeleteDeniedIP";
        fo_obj.ruleset.value = 'deleteDeniedIp';
        fo_obj.submit();
    }
    return false;
}

/**
 * @brief Remove forbidden word
 **/
function doDeleteDeniedWord(word) {
    if (confirm(xe.lang.confirm_delete)){
        var fo_obj = get_by_id('spamfilterDelete');
        fo_obj.word.value = word;
        fo_obj.act.value = "procSpamfilterAdminDeleteDeniedWord";
        fo_obj.ruleset.value = 'deleteDeniedWord';
        fo_obj.submit();
    }
}
function doInsertDeniedIP(msg_invalid_format){
	var fo_obj = get_by_id('spamfilterInsert');
	var reg_ipaddress = /^((\d{1,3}(?:.(\d{1,3}|\*)){3})\s*(\/\/(.*)\s*)?)*\s*$/;
    var regipV6 = /^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/;
	var matchStr_ipaddress = fo_obj.ipaddress_list.value;
	if(!matchStr_ipaddress.match(reg_ipaddress) && !matchStr_ipaddress.match(regipV6)) {
		alert(msg_invalid_format); return false;
	}
	fo_obj.act.value = "procSpamfilterAdminInsertDeniedIP";
	fo_obj.ruleset.value = "insertDeniedIp";
	fo_obj.submit();
}
function doInsertDeniedWord(msg_invalid_format){
	var fo_obj = get_by_id('spamfilterInsert');
	var reg_word = /^(.{2,40}\s*)*$/;
	var matchStr_word = fo_obj.word_list.value;
	if(!matchStr_word.match(reg_word)) { 
		alert(msg_invalid_format); return false;
	}
	fo_obj.act.value = "procSpamfilterAdminInsertDeniedWord";
	fo_obj.ruleset.value = "insertDeniedWord";
	fo_obj.submit();
}

