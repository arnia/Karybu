<?php
    /**
     * @class  materialView
     * @author NHN (developers@xpresseinge.com)
     * @brief  material 모듈의 View class
     **/

    class materialView extends material {

        /**
         * @brief 초기화
         **/
        function init() {
            $template_path = sprintf("%sskins/%s/",$this->module_path, $this->module_info->skin);
            if(!is_dir($template_path)||!$this->module_info->skin) {
                $this->module_info->skin = 'default';
                $template_path = sprintf("%sskins/%s/",$this->module_path, $this->module_info->skin);
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
		 * @brief 글감 수집기 팝업
		 **/
		function dispMaterialPopup() {
			global $lang;

			$mid = Context::get('mid');
			$auth = Context::get('auth');
			Context::set('site_module_info',null);
			$oMaterialModel = &getModel('material');
			$oModuleModel = &getModel('module');

			$module_info = $oModuleModel->getModuleInfoByMid($mid,$site_srl=0);
			$module_srl = $module_info->module_srl;
			$member_srl = $oMaterialModel->getMemberSrlByAuth($auth);

			if(!$member_srl) Context::set('error',true);
			
			// 템플릿 변수
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

			// 템플릿 지정
            $this->setLayoutFile("popup_layout");
			$this->setTemplateFile('popup');

            Context::addJsFilter($this->module_path.'tpl/filter', 'insert_material.xml');
		}

    }

?>
