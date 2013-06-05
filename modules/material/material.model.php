<?php
    /**
     * @class  materialModel
     * @author Arnia (developers@xpresseinge.com)
     * @brief  material 모듈의 Model class
     **/

    class materialModel extends material {

        /**
         * @brief Initialization
         **/
        function init() {
        }


        /**
         * @brief Material list return
         **/
		function getMaterialList($obj){
            if(!isset($obj->sort_index) || !in_array($obj->sort_index, array('material_srl'))) {
                $sort_index = 'material_srl';
            }
            $args = new stdClass();
            $args->module_srl = isset($obj->module_srl) ? $obj->module_srl : null;
			
			if(!$obj->member_srl){
				$logged_info = Context::get('logged_info');
				$obj->member_srl = $logged_info->member_srl;
			}
			
			$args->member_srl = $obj->member_srl;
            $args->sort_index = $sort_index;
            $args->list_count = $obj->list_count ? $obj->list_count : 20;
            $args->page = $obj->page ? $obj->page : 1;
            $output = executeQueryArray('material.getMaterialList', $args);

            return $output;
        }

        /**
         * @brief Material return
         **/
        function getMaterial($material_srl=0) {
            $args->material_srl = $material_srl;
            $output = executeQueryArray('material.getMaterial', $args);

            return $output;
        }

        /**
         * @brief Material member_srl return
         **/
		function getMemberSrlByAuth($auth){
            $args = new stdClass();
			$args->auth = $auth;
			$output = executeQuery('material.getMaterialAuth',$args);
			if($output->data) return $output->data->member_srl;
			else return null;
		}


        /**
         * @brief Material Auth return
         **/
		function getAuthByMemberSrl($member_srl){
            $args = new stdClass();
			$args->member_srl = $member_srl;
			$output = executeQuery('material.getMaterialAuth',$args);
			if($output->data) return $output->data->auth;
			else return null;
		}

        /**
         * @brief bookmark url return 
         **/
		function getBookmarkUrl($member_srl) {
			if(!$member_srl) return '';

			$base_url = Context::getDefaultUrl();
			if(!$base_url) $base_url = Context::getRequestUrl();

			$html_url = str_replace('&amp;','&', $base_url .'?act=dispMaterialPopup&module=material');
			$js_url = Context::getRequestUri().'modules/material/tpl/js/material_grabber.js';

			$auth = $this->getAuthByMemberSrl($member_srl);

			if(!$auth){
				$oMaterialController = &getController('material');
				$output = $oMaterialController->insertMaterialAuth($member_srl);
				$auth = $this->getAuthByMemberSrl($member_srl);
			}

			$bookmark_url = "javascript:(function(){var w=window,d=document,x=w.open('about:blank','XE_materialGrabWin','width=300,height=0,location=0,scrollbars=0,toolbar=0,status=0,menubar=0,resizable'),s=d.createElement('script');s.setAttribute('src','".$js_url."');w.auth='".$auth."';w.__xe_root='".$html_url."';d.body.appendChild(s);w.setTimeout(function(){x.focus()},100);})();";
			return $bookmark_url;
		}

   }
?>
