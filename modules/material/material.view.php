<?php
    /**
     * @class  materialView
     * @author Arnia (developers@xpresseinge.com)
     * @brief  material 모듈의 View class
     **/

    class materialView extends material {

        /**
         * @brief Initialization
         **/
        function init() {
            $modulePath = isset($this->module_path) ? $this->module_path : null;
            $skin = isset($this->module_info->skin) ? $this->module_info->skin  : null;
            $template_path = sprintf("%sskins/%s/",$modulePath, $skin);
            if(!is_dir($template_path) || !$skin) {
                $this->module_info->skin = 'default';
                $template_path = sprintf("%sskins/%s/",$modulePath, $this->module_info->skin);
            }
            $this->setTemplatePath($template_path);
        }

		/**
		 * @brief material list
		 **/
		function dispMaterialList(){
			$var = Context::getRequestVars();
			$args->list_count = $var->list_count;
			$args->page = $var->page;	
			$args->module_srl = $this->module_srl;
			$oMaterialModel = &getModel('material');
			$output = $oMaterialModel->getMaterialList($args);

			Context::set('material_list',$output->data);
			Context::set('page_navigation',$output->page_navigation);
		}

		/**
		 * @brief Geulgam pop Collector
		 **/
		function dispMaterialPopup() {
			global $lang;

			$mid = Context::get('mid');
			$auth = Context::get('auth');
			Context::set('site_module_info',null);
			$oMaterialModel = &getModel('material');
			$oModuleModel = &getModel('module');

			$module_info = $oModuleModel->getModuleInfoByMid($mid,$site_srl=0);
			$module_srl = isset($module_info->module_srl) ? $module_info->module_srl : null;
			$member_srl = $oMaterialModel->getMemberSrlByAuth($auth);

			if(!$member_srl) Context::set('error',true);
			
			// Template variables
			$objects = explode("\t", Context::get('objects'));
			$images  = explode("\t", Context::get('images'));

			$objects = array_unique($objects);
			$images  = array_unique($images);

			$img = array();
			foreach($images as $key => $image){
				if(preg_match('/\.(gif|jpg|jpeg|png)(\?.*|)$/i',$image)) $img[] = $image;
			}
			Context::set('objects', $objects);
			Context::set('images',  $img);

			if(!Context::get('title')) Context::set('title',Context::get('url'));

			Context::setBrowserTitle($lang->material->popup_title);

			// Templating
            $this->setLayoutFile("popup_layout");
			$this->setTemplateFile('popup');

            Context::addJsFilter($this->module_path.'tpl/filter', 'insert_material.xml');
		}

    }

?>
