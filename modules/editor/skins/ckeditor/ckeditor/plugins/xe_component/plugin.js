CKEDITOR.plugins.add('xe_component', {
    
    requires: 'richcombo',
    
    icons: 'xe_component',
    
    init: function(editor){
        var config=editor.config;
        editor.ui.addRichCombo('Xe_component', {
            label: 'Karybu',
            title: 'Extension Components',
            panel: {
                css: [CKEDITOR.skin.getPath('editor')].concat(config.contentsCss),
                multiSelect: false
            },
            init: function(){
                this.startGroup('Extension Components');
                for(var key in config.xe_component_arrays){
                    var component_name=key;
                    var component_title=config.xe_component_arrays[key];
                    this.add(component_name, component_title, component_title);
                }
            },
            onClick: function(value){
                if(typeof openComponent=='function'){
                    if(config.xe_editor_sequence)
                        openComponent(value, config.xe_editor_sequence);
                    else
                        alert('Editor sequence is undifined.');
                }
                else{
                    alert('There is no implemented function for openComponent(component_name, editor_sequence).');
                }
            }
        });
    }
});