(function() {
	
	tinymce.PluginManager.requireLangPack('xe_component');

	tinymce.create('tinymce.plugins.XeComponentPlugin', {
		createControl : function(n, cm) {
                    switch(n){
                        case 'xe_component':
                            var xe_component_array=tinyMCE.activeEditor.getParam('xe_component_arrays');
                            var xe_editor_sequence=tinyMCE.activeEditor.getParam('xe_editor_sequence');
                            var c = cm.createSplitButton('xe_component', {
                                        title : 'XE Components'
                                    });
                                    
                            c.onRenderMenu.add(function(c, m){
                                for(var key in xe_component_array){
                                    m.add({title : xe_component_array[key], onclick: function(){
                                        alert('Component name: ' + key + '\nComponent title: ' + xe_component_array[key] + '\nEditor sequence: ' + xe_editor_sequence);
                                    }});
                                }
                            });
                                    
                            return c;
                    }
                    return null;
		},

	
		getInfo : function() {
			return {
				longname : 'XE Poll Plugin',
				author : 'arnia',
				authorurl : 'http://arnia.ro',
				infourl : '',
				version : "1.0"
			};
		}
	});

	tinymce.PluginManager.add('xe_component', tinymce.plugins.XeComponentPlugin);
})();