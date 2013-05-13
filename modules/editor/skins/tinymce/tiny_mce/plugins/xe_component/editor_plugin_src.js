(function() {
	
        var xe_component_plugin_path='';
        
	tinymce.PluginManager.requireLangPack('xe_component');

	tinymce.create('tinymce.plugins.XeComponentPlugin', {
		
                init : function(ed, url) {
                    xe_component_plugin_path=url;
                },
                
                createControl : function(n, cm) {
                    switch(n){
                        case 'xe_component':
                            var xe_component_array=tinyMCE.activeEditor.getParam('xe_component_arrays');
                            var xe_editor_sequence=tinyMCE.activeEditor.getParam('xe_editor_sequence');
                            var c = cm.createSplitButton('xe_component_split_button', {title : 'XE Components', 'class': 'xe_component_icon', image: xe_component_plugin_path + '/img/ic_xe_component.gif'});
                            
                            var componentClick = function(component_name, editor_sequence){
                                return function(){
                                    if(typeof openComponent=='function'){
                                        if(editor_sequence)
                                            openComponent(component_name, editor_sequence);
                                        else
                                            alert('Editor sequence is undifined.');
                                    }
                                    else{
                                        alert('There is no implemented function for openComponent(component_name, editor_sequence).');
                                    }
                                }
                            };
                            
                            c.onRenderMenu.add(function(c, m){                                
                                for(var key in xe_component_array){
                                    var component_name=key;
                                    var component_title=xe_component_array[key];
                                    var oComponentClick=componentClick(component_name, xe_editor_sequence);
                                    m.add({title : component_title, onclick: oComponentClick});
                                }
                            });
                                    
                            return c;
                    }
                    return null;
		},

	
		getInfo : function() {
			return {
				longname : 'XE Components Plugin',
				author : 'Arnia',
				authorurl : 'http://www.karybu.org/',
				infourl : '',
				version : "1.0"
			};
		}
	});

	tinymce.PluginManager.add('xe_component', tinymce.plugins.XeComponentPlugin);
})();