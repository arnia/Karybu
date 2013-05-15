<?php
	/**
	 * @class  menuAdminModel
	 * @brief admin model class of the menu module
	 *
	 * @author Arnia (dev@karybu.org)
	 * @package /modules/menu
	 * @version 0.1
	 */
    class menuAdminModel extends menu {
		/**
		 * Initialization
		 * @return void
		 */
        function init() {
        }

		/**
		 * Get a list of all menus
		 * @param object $obj
		 * @return object
		 */
        function getMenuList($obj) {
            if(!$obj->site_srl) {
                $site_module_info = Context::get('site_module_info');
                $obj->site_srl = (int)$site_module_info->site_srl;
            }
            $args->site_srl = $obj->site_srl;
            $args->sort_index = $obj->sort_index;
            $args->page = $obj->page?$obj->page:1;
            $args->list_count = $obj->list_count?$obj->list_count:20;
            $args->page_count = $obj->page_count?$obj->page_count:10;
            // document.getDocumentList query execution
            $output = executeQuery('menu.getMenuList', $args);
            // Return if no result or an error occurs
            if(!$output->toBool()||!count($output->data)) return $output;

            return $output;
        }

		/**
		 * Return all menus
		 * @param int $site_srl
		 * @return array
		 */
        function getMenus($site_srl = null) {
            if(!isset($site_srl)) {
                $site_module_info = Context::get('site_module_info');
                $site_srl = (int)$site_module_info->site_srl;
            }
            // Get information from the DB
            $args->site_srl = $site_srl ;
            $args->menu_srl = $menu_srl;
            $output = executeQueryArray('menu.getMenus', $args);
            if(!$output->data) return;
            $menus = $output->data;
            return $menus;
        }

		/**
		 * Get information of a new menu from the DB
		 * Return DB and XML information of the menu
		 * @param int $menu_srl
		 * @return object
		 */
        function getMenu($menu_srl) {
            // Get information from the DB
            $args = new stdClass();
            $args->menu_srl = $menu_srl;
            $output = executeQuery('menu.getMenu', $args);
            if(!$output->data) {
                return;
            }

            $menu_info = $output->data;
            $menu_info->xml_file = sprintf('./files/cache/menu/%s.xml.php',$menu_srl);
            $menu_info->php_file = sprintf('./files/cache/menu/%s.php',$menu_srl);
            return $menu_info;
        }

		/**
		 * Get information of a new menu from the DB, search condition is menu title
		 * Return DB and XML information of the menu
		 * @param string $title
		 * @return object
		 */
        function getMenuByTitle($title) {
            // Get information from the DB
            $args = new stdClass();
            $args->title = $title;
            $output = executeQuery('menu.getMenuByTitle', $args);
            if(!$output->data) {
                return null;
            }

			if(is_array($output->data)) $menu_info = $output->data[0];
			else $menu_info = $output->data;

            if($menu_info->menu_srl)
			{
				$menu_info->xml_file = sprintf('./files/cache/menu/%s.xml.php',$menu_info->menu_srl);
	            $menu_info->php_file = sprintf('./files/cache/menu/%s.php',$menu_info->menu_srl);
			}
            return $menu_info;
        }

		/**
		 * Return item information of the menu_srl
		 * group_srls uses a seperator with comma(,) and converts to an array by explode
		 * @param int $menu_item_srl
		 * @return object
		 */
        function getMenuItemInfo($menu_item_srl) {
            // Get the menu information if menu_item_srl exists
            $args->menu_item_srl = $menu_item_srl;
            $output = executeQuery('menu.getMenuItem', $args);
            $node = $output->data;
			settype($node,'object');
            if($node->group_srls) $node->group_srls = explode(',',$node->group_srls);
            else $node->group_srls = array();

            $tmp_name = unserialize($node->name);
            if($tmp_name && count($tmp_name) ) {
                $selected_lang = array();
                $rand_name = $tmp_name[Context::getLangType()];
                if(!$rand_name) $rand_name = array_shift($tmp_name);
                $node->name = $rand_name;
            }
            return $node;
        }

		/**
		 * Return item information of the menu_srl
		 * @return void
		 */
        function getMenuAdminItemInfo()
		{
			$menuItemSrl = Context::get('menu_item_srl');
			$menuItem = $this->getMenuItemInfo($menuItemSrl);

			if(!$menuItem->url)
			{
				$menuItem->moduleType = null;
			}
			else if(!preg_match('/^http/i',$menuItem->url))
			{
				$oModuleModel = &getModel('module');
				$moduleInfo = $oModuleModel->getModuleInfoByMid($menuItem->url, 0);
				if(!$moduleInfo) $menuItem->moduleType = 'url';
				else
				{
					if($moduleInfo->mid == $menuItem->url) {
						$menuItem->moduleType = $moduleInfo->module;
						$menuItem->pageType = $moduleInfo->page_type;
						$menuItem->layoutSrl = $moduleInfo->layout_srl;
					}
				}
			}
			else $menuItem->moduleType = 'url';

			// get groups
			$oMemberModel = &getModel('member');
			$oModuleAdminModel = &getAdminModel('module');
			$output = $oMemberModel->getGroups();
			if(is_array($output))
			{
				$groupList = array();
				foreach($output AS $key=>$value)
				{

					$groupList[$value->group_srl]->group_srl = $value->group_srl;
            		if(substr($value->title,0,12)=='$user_lang->') {
						$tmp = $oModuleAdminModel->getLangCode(0, $value->title);
						$groupList[$value->group_srl]->title = $tmp[Context::getLangType()];
					}
					else $groupList[$value->group_srl]->title = $value->title;

					if(in_array($key, $menuItem->group_srls)) $groupList[$value->group_srl]->isChecked = true;
					else $groupList[$value->group_srl]->isChecked = false;
				}
			}
			$menuItem->groupList = $groupList;

			$oModuleController = &getController('module');
			$menuItem->name_key = $menuItem->name;
			$oModuleController->replaceDefinedLangCode($menuItem->name);

			$this->add('menu_item', $menuItem);
        }

		/**
		 * Return menu item list by menu number
		 * @param int $menu_srl
		 * @param int $parent_srl
		 * @param array $columnList
		 * @return object
		 */
		function getMenuItems($menu_srl, $parent_srl = null, $columnList = array())
		{
            $args = new stdClass();
			$args->menu_srl = $menu_srl;
			$args->parent_srl = $parent_srl;

			$output = executeQueryArray('menu.getMenuItems', $args, $columnList);
			return $output;
		}

		/**
		 * Return menu name in each language to support multi-language
		 * @param string $source_name
		 * @param int $site_srl
		 * @return array
		 */
        function getMenuItemNames($source_name, $site_srl = null) {
            if(!$site_srl) {
                $site_module_info = Context::get('site_module_info');
                $site_srl = (int)$site_module_info->site_srl;
            }
            // Get language code
            $oModuleAdminModel = &getAdminModel('module');
            return $oModuleAdminModel->getLangCode($site_srl, $source_name);
        }

		/**
		 * @brief when menu add in sitemap, select module list
		 * this menu showing with trigger
		 * @param int $site_srl
		 * @return array
		 */
		function getModuleListInSitemap($site_srl = 0)
		{
			$oModuleModel = &getModel('module');
			$columnList = array('module');
			$moduleList = array('page');

			$output = $oModuleModel->getModuleListByInstance($site_srl, $columnList);
			if(is_array($output->data))
			{
				foreach($output->data AS $key=>$value)
				{
					array_push($moduleList, $value->module);
				}
			}

            // after trigger
            $output = ModuleHandler::triggerCall('menu.getModuleListInSitemap', 'after', $moduleList);
            if(!$output->toBool()) return $output;

			$moduleList = array_unique($moduleList);

			$moduleInfoList = array();
			if(is_array($moduleList))
			{
				foreach($moduleList AS $key=>$value)
				{
					$moduleInfo = $oModuleModel->getModuleInfoXml($value);
					$moduleInfoList[$value] = $moduleInfo;
				}
			}

            return $moduleInfoList;
		}
    }
?>
