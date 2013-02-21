function deleteMaterialItem(material_srl){
	var params = new Array();
	params['mid'] = current_mid;
    if(material_srl) {
        params['material_srl'] = material_srl;
    } else {
        var selectedEl = jQuery('#content .subjectList input:checked.materialCart');
        var material_srls = [];
        selectedEl.each(function() {
            material_srls.push(this.value);
        });
        params['material_srl'] = material_srls.join(',');
    }

	var response_tags = new Array('error','message','page','mid');
	exec_xml('material', 'procMaterialDelete', params, completeReload, response_tags);
}

function deleteMaterialItem(material_srl){
	var params = new Array();
	params['mid'] = current_mid;
    if(material_srl) {
        params['material_srl'] = material_srl;
    } else {
        var selectedEl = jQuery('.ingredientList .ingredientNav input:checked.ingredientCart');
        var material_srls = [];
        selectedEl.each(function() {
            material_srls.push(this.value);
        });
        params['material_srl'] = material_srls.join(',');
    }

	var response_tags = new Array('error','message','page','mid');
	exec_xml('material', 'procMaterialDelete', params, completeReload, response_tags);
}

function completeReload(ret_obj){
    location.href = location.href;
}

