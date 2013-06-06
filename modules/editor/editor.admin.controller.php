<?php
    /**
     * @class  editorAdminController
     * @author Arnia (dev@karybu.org)
     * @brief editor of the module admin controller class
     **/

    class editorAdminController extends editor {

        /**
         * @brief Initialization
         **/
        function init() {
        }
		
		/**
         * @brief Enabling components, change the list order
         **/	
		function procEditorAdminCheckUseListOrder(){
			$site_module_info = Context::get('site_module_info');
			$enables = Context::get('enables');			
			$component_names = Context::get('component_names');

			if(!is_array($component_names)) $component_names = array();
			if(!is_array($enables)) $enables = array();

			$unables = array_diff($component_names, $enables);
			$componentList = array();	
			
			foreach($enables as $component_name) {
				$componentList[$component_name] = 'Y';
			}
			foreach($unables as $component_name) {
				$componentList[$component_name] = 'N';
			}

			$output = $this->editorListOrder($component_names,$site_module_info->site_srl);
			if(!$output->toBool()) return new Object();
			
			$output = $this->editorCheckUse($componentList,$site_module_info->site_srl);			
			if(!$output->toBool()) return new Object();
			
			$oEditorController = &getController('editor');
            $oEditorController->removeCache($site_module_info->site_srl);
			$this->setRedirectUrl(Context::get('error_return_url'));
		}
		
		/**
         * @brief check use component
         **/	
		function editorCheckUse($componentList, $site_srl = 0){			
			$args->site_srl = $site_srl;
			
			foreach($componentList as $componentName => $value){
				$args->component_name = $componentName;				
				$args->enabled = $value;
				if($site_srl == 0) {
					$output = executeQuery('editor.updateComponent', $args);
				} else {
					$output = executeQuery('editor.updateSiteComponent', $args);
				}
			}
			if(!$output->toBool()) return new Object();
			
			unset($componentList);
			return $output;
		}
		
		/**
         * @brief list order componet
         **/
		function editorListOrder($component_names, $site_srl = 0){		
			$args->site_srl = $site_srl;
			$list_order_num = '30';
			if(is_array($component_names)) {			
				foreach($component_names as $name){
					$args->list_order = $list_order_num;
					$args->component_name = $name;
					if($site_srl == 0) {
						$output = executeQuery('editor.updateComponent', $args);					
					} else {
						$output = executeQuery('editor.updateSiteComponent', $args);					
					}

			
					if(!$output->toBool()) return new Object();
					$list_order_num++;
				}
			}	
			unset($component_names);
			return $output;
		}

        /**
         * @brief Set components
         **/
        function procEditorAdminSetupComponent() {
            $site_module_info = Context::get('site_module_info');

            $component_name = Context::get('component_name');
            $extra_vars = Context::getRequestVars();
            unset($extra_vars->component_name);
            unset($extra_vars->module);
            unset($extra_vars->act);
            unset($extra_vars->body);

            if($extra_vars->target_group) $extra_vars->target_group = explode('|@|', $extra_vars->target_group);
            if($extra_vars->mid_list) $extra_vars->mid_list = explode('|@|', $extra_vars->mid_list);

            $args->component_name = $component_name;
            $args->extra_vars = serialize($extra_vars);
            $args->site_srl = (int)$site_module_info->site_srl;

            if(!$args->site_srl) $output = executeQuery('editor.updateComponent', $args);
            else $output = executeQuery('editor.updateSiteComponent', $args);
            if(!$output->toBool()) return $output;

            $oEditorController = &getController('editor');
            $oEditorController->removeCache($args->site_srl);

            $this->setMessage('success_updated');
			$this->setRedirectUrl(Context::get('error_return_url'));
        }
		
		/**
         * @brief Config components
         **/
		 
		function procEditorAdminGeneralConfig(){
			$oModuleController = &getController('module');
			$configVars = Context::getRequestVars();
			$config = new stdClass();
			$config->editor_skin = isset($configVars->editor_skin) ? $configVars->editor_skin : null;
			$config->editor_height = isset($configVars->editor_height) ? $configVars->editor_height  : null;
			$config->comment_editor_skin = isset($configVars->comment_editor_skin) ? $configVars->comment_editor_skin : null;
			$config->comment_editor_height = isset($configVars->comment_editor_height) ? $configVars->comment_editor_height : null;
			$config->content_style = isset($configVars->content_style) ? $configVars->content_style : null;
			$config->content_font = isset($configVars->content_font) ? $configVars->content_font : null;
			$config->content_font_size= isset($configVars->content_font_size) ? $configVars->content_font_size.'px' : 0;
			$config->sel_editor_colorset= isset($configVars->sel_editor_colorset) ? $configVars->sel_editor_colorset : null;
			$config->sel_comment_editor_colorset= isset($configVars->sel_comment_editor_colorset) ? $configVars->sel_comment_editor_colorset : null;
			
			$oModuleController->insertModuleConfig('editor',$config);
			$this->setRedirectUrl(Context::get('error_return_url'));
					
		}

        /**
         * @brief Add a component to DB
         **/
        function insertComponent($component_name, $enabled = false, $site_srl = 0) {
            if($enabled) {
                $enabled = 'Y';
            }
            else {
                $enabled = 'N';
            }
            $args = new stdClass();
            $args->component_name = $component_name;
            $args->enabled = $enabled;
            $args->site_srl = $site_srl;
            // Check if the component exists
            if(!$site_srl) $output = executeQuery('editor.isComponentInserted', $args);
            else $output = executeQuery('editor.isSiteComponentInserted', $args);
            if($output->data->count) return new Object(-1, 'msg_component_is_not_founded');
            // Inert a component
            $args->list_order = getNextSequence();
            if(!$site_srl) $output = executeQuery('editor.insertComponent', $args);
            else $output = executeQuery('editor.insertSiteComponent', $args);

            $oEditorController = &getController('editor');
            $oEditorController->removeCache($site_srl);
            return $output;
        }
    }
?>
