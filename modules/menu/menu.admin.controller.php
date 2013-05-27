<?php
	/**
	 * menuAdminController class
	 * admin controller class of the menu module
	 *
	 * @author Arnia (dev@karybu.org)
	 * @package /modules/menu
	 * @version 0.1
	 */
    class menuAdminController extends menu {
		/**
		 * menu number
		 * @var int
		 */
		var $menuSrl = null;
		/**
		 * item key list
		 * @var array
		 */
		var $itemKeyList = array();
		/**
		 * map
		 * @var array
		 */
		var $map = array();
		/**
		 * checked
		 * @var array
		 */
		var $checked = array();

		/**
		 * Initialization
		 * @return void
		 */
        function init() {
        }

		/**
		 * Add a menu
		 * @return void|object
		 */
        function procMenuAdminInsert() {
            // List variables
            $site_module_info = Context::get('site_module_info');
            $args->site_srl = (int)$site_module_info->site_srl;
            $args->title = Context::get('title');
            $args->menu_srl = getNextSequence();
            $args->listorder = $args->menu_srl * -1;

            $output = executeQuery('menu.insertMenu', $args);
            if(!$output->toBool()) return $output;

            $this->add('menu_srl', $args->menu_srl);
            $this->setMessage('success_registed');

			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMenuAdminSiteMap');
			$this->setRedirectUrl($returnUrl);
        }

		/**
		 * Delete menu process method
		 * @return void|Object
		 */
        function procMenuAdminDelete() {
            $menu_srl = Context::get('menu_srl');

			$oMenuAdminModel = &getAdminModel('menu');
			$menu_info = $oMenuAdminModel->getMenu($menu_srl);

			if($menu_info->title == '__KARYBU_ADMIN__')
				return new Object(-1, 'msg_adminmenu_cannot_delete');

            $this->deleteMenu($menu_srl);

			$this->setMessage('success_deleted', 'info');

			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMenuAdminSiteMap');
			$this->setRedirectUrl($returnUrl);
        }

		/**
		 * Delete menu
		 * Delete menu_item and xml cache files
		 * @return Object
		 */
        function deleteMenu($menu_srl) {
            // Delete cache files
            $cache_list = FileHandler::readDir("./files/cache/menu","",false,true);
            if(count($cache_list)) {
                foreach($cache_list as $cache_file) {
                    $pos = strpos($cache_file, $menu_srl.'.');
                    if($pos>0)FileHandler::removeFile($cache_file);
                }
            }
            // Delete images of menu buttons
            $image_path = sprintf('./files/attach/menu_button/%s', $menu_srl);
            FileHandler::removeDir($image_path);
            $args = new stdClass();
            $args->menu_srl = $menu_srl;
            // Delete menu items
            $output = executeQuery("menu.deleteMenuItems", $args);
            if(!$output->toBool()) return $output;
            // Delete the menu
            $output = executeQuery("menu.deleteMenu", $args);
            if(!$output->toBool()) return $output;

            return new Object(0,'success_deleted');
        }

		/**
		 * Add an item to the menu
		 * @return void
		 */
        function procMenuAdminInsertItem() {
            // List variables to insert
            $source_args = Context::getRequestVars();

            unset($source_args->module);
            unset($source_args->act);
            if(!isset($source_args->menu_open_window) || $source_args->menu_open_window!="Y") {
                $source_args->menu_open_window = "N";
            }
            if(!isset($source_args->menu_expand) || $source_args->menu_expand !="Y") {
                $source_args->menu_expand = "N";
            }

			if(isset($source_args->menu_grant_default) && $source_args->menu_grant_default == -1) {
                $source_args->group_srls = -1;
            }
            if (isset($source_args->group_srls)) {
                if(!is_array($source_args->group_srls)) {
                    $source_args->group_srls = str_replace('|@|',',',$source_args->group_srls);
                }
                else {
                    $source_args->group_srls = implode(',', $source_args->group_srls);
                }
            }
            else {
                $source_args->group_srls = '';
            }

            $source_args->parent_srl = isset($source_args->parent_srl) ? (int)$source_args->parent_srl : 0;

			if($source_args->cType == 'CREATE') {
                $source_args->menu_url = $source_args->create_menu_url;
            }
			elseif($source_args->cType == 'SELECT') {
                $source_args->menu_url = $source_args->select_menu_url;
            }

			// upload button
			$btnOutput = $this->_uploadButton($source_args);

            // Re-order variables (Column's order is different between form and DB)
            $args = new stdClass();
            $args->menu_srl = isset($source_args->menu_srl) ? $source_args->menu_srl : null;
            $args->menu_item_srl = isset($source_args->menu_item_srl) ? $source_args->menu_item_srl : null;
            $args->parent_srl = isset($source_args->parent_srl) ? $source_args->parent_srl : null;
            $args->menu_srl = isset($source_args->menu_srl) ? $source_args->menu_srl : null;
            $args->menu_id = isset($source_args->menu_id) ? $source_args->menu_id : null;

			if (!empty($source_args->menu_name_key)) {
	            $args->name = $source_args->menu_name_key;
            }
			else {
				$args->name = $source_args->menu_name;
            }

			if(!strstr($args->name, '$user_lang->'))
			{
				$args->name = htmlspecialchars($args->name);
			}

            $args->url = trim($source_args->menu_url);
            $args->open_window = $source_args->menu_open_window;
            $args->expand = $source_args->menu_expand;
            if(!empty($btnOutput['normal_btn'])) {
                $args->normal_btn = $btnOutput['normal_btn'];
            }
            if(!empty($btnOutput['hover_btn'])) {
                $args->hover_btn = $btnOutput['hover_btn'];
            }
            if(!empty($btnOutput['active_btn'])) {
                $args->active_btn = $btnOutput['active_btn'];
            }
            $args->group_srls = $source_args->group_srls;

			// if cType is CREATE, create module
			if($source_args->cType == 'CREATE' || $source_args->cType == 'SELECT')
			{
				$site_module_info = Context::get('site_module_info');
                $cmArgs = new stdClass();
				$cmArgs->site_srl = (int)$site_module_info->site_srl;
				$cmArgs->browser_title = $args->name;
				$cmArgs->menu_srl = $source_args->menu_srl;
				if($source_args->layout_srl)
				{
					$cmArgs->layout_srl = $source_args->layout_srl;
				}

				switch ($source_args->module_type){
					case 'WIDGET' :
					case 'ARTICLE' :
					case 'OUTSIDE' :
						$cmArgs->module = 'page';
						$cmArgs->page_type = $source_args->module_type;
						break;
					default:
						$cmArgs->module = $source_args->module_type;
						unset($cmArgs->page_type);
				}

                $oModuleController = &getController('module');
				if($source_args->cType == 'CREATE')
				{
					$cmArgs->mid = $source_args->create_menu_url;
					$output = $oModuleController->insertModule($cmArgs);
				}
				else
				{
					$oModuleModel = &getModel('module');
					$module_info = $oModuleModel->getModuleInfoByModuleSrl($source_args->module_srl);
					if($cmArgs->layout_srl)
					{
						$module_info->layout_srl = $cmArgs->layout_srl;
					}
					$cmArgs = $module_info;

					$cmArgs->mid = $source_args->select_menu_url;
					$cmArgs->module_srl = $source_args->module_srl;
					$output = $oModuleController->updateModule($cmArgs);
				}
				if(!$output->toBool()) return new Object(-1, $output->message);
			}

            // Check if already exists
            $oMenuModel = &getAdminModel('menu');
            $item_info = $oMenuModel->getMenuItemInfo($args->menu_item_srl);

			// button is deleted, db delete
			if(isset($source_args->isNormalDelete) && $source_args->isNormalDelete == 'Y') {
                $args->normal_btn = '';
            }
			if(isset($source_args->isHoverDelete) && $source_args->isHoverDelete == 'Y') {
                $args->hover_btn = '';
            }
			if(isset($source_args->isActiveDelete) && $source_args->isActiveDelete == 'Y') {
                $args->active_btn = '';
            }

			$message = '';
            // Update if exists
            if(!empty($args->menu_item_srl) && $item_info->menu_item_srl == $args->menu_item_srl) {
                $output = executeQuery('menu.updateMenuItem', $args);
                if(!$output->toBool()) return $output;
				$message = 'success_updated';
            // Insert if not exist
            } else {
				if(!$args->menu_item_srl) $args->menu_item_srl = getNextSequence();
                $args->listorder = -1*$args->menu_item_srl;
                $output = executeQuery('menu.insertMenuItem', $args);
                if(!$output->toBool()) return $output;
				$message = 'success_registed';
            }
            // Get information of the menu
            $menu_info = $oMenuModel->getMenu($args->menu_srl);
            $menu_title = $menu_info->title;
            // Update the xml file and get its location
            $xml_file = $this->makeXmlFile($args->menu_srl);
            // If a new menu item that mid is URL is added, the current layout is applied
            if(preg_match('/^([a-zA-Z0-9\_\-]+)$/', $args->url)) {
                $mid = $args->url;
                $mid_args = new stdClass();
                $mid_args->menu_srl = $args->menu_srl;
                $mid_args->mid = $mid;
                // Get layout value of menu_srl
                $output = executeQuery('menu.getMenuLayout', $args);
                // Set if layout value is not specified in the module
                $oModuleModel = &getModel('module');
				$columnList = array('layout_srl');
                $module_info = $oModuleModel->getModuleInfoByMid($mid, 0, $columnList);
                if(!$module_info->layout_srl&&$output->data->layout_srl) $mid_args->layout_srl = $output->data->layout_srl;
                // Change menu value of the mid to the menu
                $oModuleController = &getController('module');
                $oModuleController->updateModuleMenu($mid_args);
            }

            $this->add('xml_file', $xml_file);
            $this->add('menu_srl', $args->menu_srl);
            $this->add('menu_item_srl', $args->menu_item_srl);
            $this->add('menu_title', $menu_title);
            $this->add('parent_srl', $args->parent_srl);

			$this->setMessage($message, 'info');

			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMenuAdminSiteMap', 'menu_srl', $args->menu_srl);
			$this->setRedirectUrl($returnUrl);
        }

		/**
		 * Delete menu item(menu of the menu)
		 * @return void|Object
		 */
        function procMenuAdminDeleteItem() {
            // List variables
            $args = Context::gets('menu_srl','menu_item_srl');

            $oMenuAdminModel = &getAdminModel('menu');

            // Get information of the menu
            $menu_info = $oMenuAdminModel->getMenu($args->menu_srl);
            $menu_title = $menu_info->title;

            // Get original information
            $item_info = $oMenuAdminModel->getMenuItemInfo($args->menu_item_srl);

			if($menu_title == '__KARYBU_ADMIN__' && $item_info->parent_srl == 0)return $this->stop('msg_cannot_delete_for_admin_topmenu');

            if($item_info->parent_srl) $parent_srl = $item_info->parent_srl;
            // Display an error that the category cannot be deleted if it has a child node
            $output = executeQuery('menu.getChildMenuCount', $args);
            if(!$output->toBool()) return $output;
            if($output->data->count>0) return new Object(-1, 'msg_cannot_delete_for_child');
            // Remove from the DB
            $output = executeQuery("menu.deleteMenuItem", $args);
            if(!$output->toBool()) return $output;
            // Update the xml file and get its location
            $xml_file = $this->makeXmlFile($args->menu_srl);
            // Delete all of image buttons
            if($item_info->normal_btn) FileHandler::removeFile($item_info->normal_btn);
            if($item_info->hover_btn) FileHandler::removeFile($item_info->hover_btn);
            if($item_info->active_btn) FileHandler::removeFile($item_info->active_btn);

            $this->add('xml_file', $xml_file);
            $this->add('menu_title', $menu_title);
            $this->add('menu_item_srl', isset($parent_srl) ? $parent_srl : null);
            $this->setMessage('success_deleted');

			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMenuAdminSiteMap') . '#menuTop_' . $args->menu_srl;
			$this->setRedirectUrl($returnUrl);
        }

		/**
		 * Arrange menu items
		 * @return void|object
		 */
		function procMenuAdminArrangeItem()
		{
			$this->menuSrl = Context::get('menu_srl');
            $args = new stdClass();
            $args->title = Context::get('title');
			$parentKeyList = Context::get('parent_key');
			$this->itemKeyList = Context::get('item_key');

			// menu name update
            $args->menu_srl = $this->menuSrl;
            $output = executeQuery('menu.updateMenu', $args);
            if(!$output->toBool()) return $output;

			$this->map = array();
			if(is_array($parentKeyList))
			{
				foreach($parentKeyList as $no=>$srl)
				{
					if ($srl === 0) {
                        continue;
                    }
					if (!isset($this->map[$srl]) || !is_array($this->map[$srl])){
                        $this->map[$srl] = array();
                    }
					$this->map[$srl][] = $no;
				}
			}

			$result = array();
			if(is_array($this->itemKeyList))
			{
				foreach($this->itemKeyList as $srl)
				{
					if (empty($this->checked[$srl])) {
						$target = new stdClass();
						$this->checked[$srl] = 1;
						$target->node = $srl;
						$target->child= array();

						while(isset($this->map[$srl]) && count($this->map[$srl])){
							$this->_setParent($srl, array_shift($this->map[$srl]), $target);
						}
						$result[] = $target;
					}
				}
			}

			if(is_array($result))
			{
				$i = 0;
				foreach($result AS $key=>$node)
				{
					$this->moveMenuItem($this->menuSrl, 0, $i, $node->node, 'move');	//move parent node
					$this->_recursiveMoveMenuItem($node);	//move child node
					$i = $node->node;
				}
			}

            $this->setMessage('success_updated', 'info');

			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrlgetNotEncodedUrl('', 'module', 'admin', 'act', 'dispMenuAdminSiteMap') . '#menuTop_' . $args->menu_srl;
			$this->setRedirectUrl($returnUrl);
		}

		/**
		 * Set parent number to child
		 * @param int $parent_srl
		 * @param int $child_index
		 * @param object $target
		 * @return void
		 */
		function _setParent($parent_srl, $child_index, &$target)
		{
			$child_srl = $this->itemKeyList[$child_index];
			$this->checked[$child_srl] = 1;

			$child_node->node = $child_srl;
			$child_node->parent_node = $parent_srl;
			$child_node->child = array();
			$target->child[] = $child_node;

			while(count($this->map[$child_srl])){
				$this->_setParent($child_srl, array_shift($this->map[$child_srl]), $child_node);
			}
			//return $target;
		}

		/**
		 * move item with sub directory(recursive)
		 * @param object $result
		 * @return void
		 */
		function _recursiveMoveMenuItem($result)
		{
			$i = 0;
			while(count($result->child))
			{
				unset($node);
				$node = array_shift($result->child);

				$this->moveMenuItem($this->menuSrl, $node->parent_node, $i, $node->node, 'move');
				$this->_recursiveMoveMenuItem($node);
				$i = $node->node;
			}
		}

		/**
		 * move menu item
		 * @param int $menu_srl
		 * @param int $parent_srl
		 * @param int $source_srl
		 * @param int $target_srl
		 * @param string $mode 'move' or 'insert'
		 * @return void
		 */
        function moveMenuItem($menu_srl,$parent_srl,$source_srl,$target_srl,$mode){
            // Get the original menus
            $oMenuAdminModel = &getAdminModel('menu');

            $target_item = $oMenuAdminModel->getMenuItemInfo($target_srl);
            if($target_item->menu_item_srl != $target_srl) return new Object(-1,'msg_invalid_request');
            // Move the menu location(change the order menu appears)
            if($mode == 'move') {
                $args = new stdClass();
                $args->parent_srl = $parent_srl;
                $args->menu_srl = $menu_srl;

                if($source_srl) {
                    $source_item = $oMenuAdminModel->getMenuItemInfo($source_srl);
                    if($source_item->menu_item_srl != $source_srl) return new Object(-1,'msg_invalid_request');
                    $args->listorder = $source_item->listorder-1;
                }  else {
                    $output = executeQuery('menu.getMaxListorder', $args);
                    if(!$output->toBool()) return $output;
                    $args->listorder = (int)$output->data->listorder;
                    if(!$args->listorder) $args->listorder= 0;
                }
                $args->parent_srl = $parent_srl;
                $output = executeQuery('menu.updateMenuItemListorder', $args);
                if(!$output->toBool()) return $output;

                $args->parent_srl = $parent_srl;
                $args->menu_item_srl = $target_srl;
                $output = executeQuery('menu.updateMenuItemNode', $args);
                if(!$output->toBool()) return $output;
            // Add a child
            } elseif($mode == 'insert') {
                $args = new stdClass();
                $args->menu_item_srl = $target_srl;
                $args->parent_srl = $parent_srl;
                $args->listorder = -1*getNextSequence();
                $output = executeQuery('menu.updateMenuItemNode', $args);
                if(!$output->toBool()) return $output;
            }

            $xml_file = $this->makeXmlFile($menu_srl);
            return $xml_file;
        }

		/**
		 * Update xml file
		 * XML file is not often generated after setting menus on the admin page\n
		 * For this occasional cases, manually update was implemented. \n
		 * It looks unnecessary at this moment however no need to eliminate the feature. Just leave it.
		 * @return void
		 */
        function procMenuAdminMakeXmlFile() {
            // Check input value
            $menu_srl = Context::get('menu_srl');
            // Get information of the menu
            $oMenuAdminModel = &getAdminModel('menu');
            $menu_info = $oMenuAdminModel->getMenu($menu_srl);
            $menu_title = $menu_info->title;
            // Re-generate the xml file
            $xml_file = $this->makeXmlFile($menu_srl);
            // Set return value
            $this->add('menu_title',$menu_title);
            $this->add('xml_file',$xml_file);
        }


		/**
		 * Remove the menu image button
		 * @return void
		 */
        function procMenuAdminDeleteButton() {
            $menu_srl = Context::get('menu_srl');
            $menu_item_srl = Context::get('menu_item_srl');
            $target = Context::get('target');
            $filename = Context::get('filename');
            FileHandler::removeFile($filename);

            $this->add('target', $target);
        }

		/**
		 * Get all act list for admin menu
		 * @return void
		 */
        function procMenuAdminAllActList() {
            $oModuleModel = &getModel('module');
            $installed_module_list = $oModuleModel->getModulesXmlInfo();
			if(is_array($installed_module_list))
			{
				$currentLang = Context::getLangType();
				$menuList = array();
				foreach($installed_module_list AS $key=>$value)
				{
					$info = $oModuleModel->getModuleActionXml($value->module);
					if($info->menu) $menuList[$value->module] = $info->menu;
					unset($info->menu);
				}
			}
            $this->add('menuList', $menuList);
        }

		/**
		 * Get all act list for admin menu
		 * @return void|object
		 */
		function procMenuAdminInsertItemForAdminMenu()
		{
            $requestArgs = Context::getRequestVars();
			$tmpMenuName = explode(':', $requestArgs->menu_name);
			$moduleName = $tmpMenuName[0];
			$menuName = $tmpMenuName[1];

			// variable setting
			$logged_info = Context::get('logged_info');
			//$oMenuAdminModel = &getAdminModel('menu');
			$oMemberModel = &getModel('member');

			//$parentMenuInfo = $oMenuAdminModel->getMenuItemInfo($requestArgs->parent_srl);
			$groupSrlList = $oMemberModel->getMemberGroups($logged_info->member_srl);

			//preg_match('/\{\$lang->menu_gnb\[(.*?)\]\}/i', $parentMenuInfo->name, $m);
			$oModuleModel = &getModel('module');
			//$info = $oModuleModel->getModuleInfoXml($moduleName);
			$info = $oModuleModel->getModuleActionXml($moduleName);

			$url = getNotEncodedFullUrl('', 'module', 'admin', 'act', $info->menu->{$menuName}->index);
			if(empty($url)) $url = getNotEncodedFullUrl('', 'module', 'admin', 'act', $info->admin_index_act);
			if(empty($url)) $url = getNotEncodedFullUrl('', 'module', 'admin');
			$dbInfo = Context::getDBInfo();

			$args->menu_item_srl = (!$requestArgs->menu_item_srl) ? getNextSequence() : $requestArgs->menu_item_srl;
			$args->parent_srl = $requestArgs->parent_srl;
			$args->menu_srl = $requestArgs->menu_srl;
			$args->name = sprintf('{$lang->menu_gnb_sub[\'%s\']}', $menuName);
			//if now page is https...
			if(strpos($url, 'https') !== false)
			{
				$args->url = str_replace('https'.substr($dbInfo->default_url, 4), '', $url);
			}
			else
			{
				$args->url = str_replace($dbInfo->default_url, '', $url);
			}
			$args->open_window = 'N';
			$args->expand = 'N';
			$args->normal_btn = '';
			$args->hover_btn = '';
			$args->active_btn = '';
			$args->group_srls = implode(',', array_keys($groupSrlList));
			$args->listorder = -1*$args->menu_item_srl;

            // Check if already exists
            $oMenuModel = &getAdminModel('menu');
            $item_info = $oMenuModel->getMenuItemInfo($args->menu_item_srl);
            // Update if exists
            if($item_info->menu_item_srl == $args->menu_item_srl) {
                $output = executeQuery('menu.updateMenuItem', $args);
                if(!$output->toBool()) return $output;
            }
            // Insert if not exist
			else {
                $args->listorder = -1*$args->menu_item_srl;
                $output = executeQuery('menu.insertMenuItem', $args);
                if(!$output->toBool()) return $output;
            }
            // Get information of the menu
            $menu_info = $oMenuModel->getMenu($args->menu_srl);
            $menu_title = $menu_info->title;
            // Update the xml file and get its location
            $xml_file = $this->makeXmlFile($args->menu_srl);

			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAdminSetup');
			$this->setRedirectUrl($returnUrl);
		}

		/**
		 * Generate XML file for menu and return its location
		 * @param int $menu_srl
		 * @return string
		 */
        function makeXmlFile($menu_srl) {
            // Return if there is no information when creating the xml file
            if(!$menu_srl) {
                return;
            }
            // Get menu informaton
            $args = new stdClass();
            $domain = null;
            $args->menu_srl = $menu_srl;
            $output = executeQuery('menu.getMenu', $args);
            if(!$output->toBool() || !$output->data) return $output;
            $site_srl = (int)$output->data->site_srl;

            if($site_srl) {
                $oModuleModel = &getModel('module');
				$columnList = array('sites.domain');
                $site_info = $oModuleModel->getSiteInfo($site_srl, $columnList);
                $domain = $site_info->domain;
            }
            // Get a list of menu items corresponding to menu_srl by listorder
            $args->menu_srl = $menu_srl;
            $args->sort_index = 'listorder';
            $output = executeQuery('menu.getMenuItems', $args);
            if(!$output->toBool()) return;
            // Specify the name of the cache file
            $xml_file = sprintf("./files/cache/menu/%s.xml.php", $menu_srl);
            $php_file = sprintf("./files/cache/menu/%s.php", $menu_srl);
            // If no data found, generate an XML file without node data
            $list = $output->data;
            if(!$list) {
                $xml_buff = "<root />";
                FileHandler::writeFile($xml_file, $xml_buff);
                FileHandler::writeFile($php_file, '<?php if(!defined("__KARYBU__")) exit(); ?>');
                return $xml_file;
            }
            // Change to an array if only a single data is obtained
            if(!is_array($list)) $list = array($list);
            // Create a tree for loop
            $list_count = count($list);
            for($i=0;$i<$list_count;$i++) {
                $node = $list[$i];
                $menu_item_srl = $node->menu_item_srl;
                $parent_srl = $node->parent_srl;

                $tree[$parent_srl][$menu_item_srl] = $node;
            }
            // A common header to set permissions of the cache file and groups
            $header_script =
                '$lang_type = Context::getLangType(); '.
                '$is_logged = Context::get(\'is_logged\'); '.
                '$logged_info = Context::get(\'logged_info\'); '.
                '$site_srl = '.$site_srl.';'.
                '$site_admin = false;'.
                'if($site_srl) { '.
                '$oModuleModel = &getModel(\'module\');'.
                '$site_module_info = $oModuleModel->getSiteInfo($site_srl); '.
				'if($site_module_info) Context::set(\'site_module_info\',$site_module_info);'.
				'else $site_module_info = Context::get(\'site_module_info\');'.
                '$grant = $oModuleModel->getGrant($site_module_info, $logged_info); '.
                'if($grant->manager ==1) $site_admin = true;'.
				'}'.
                'if($is_logged) {'.
                    'if($logged_info->is_admin=="Y") $is_admin = true; '.
                    'else $is_admin = false; '.
                    '$group_srls = array_keys($logged_info->group_list); '.
                '} else { '.
                    '$is_admin = false; '.
                    '$group_srls = array(); '.
                '}';
            // Create the xml cache file (a separate session is needed for xml cache)
            $xml_buff = sprintf(
                '<?php '.
                'define(\'__KARYBU__\', true); '.
                'define(\'__KARYBU__\', true); '.
                'require_once(\''.FileHandler::getRealPath('./config/config.inc.php').'\'); '.
                '$oContext = &Context::getInstance(); '.
                '$oContext->init(); '.
                'header("Content-Type: text/xml; charset=UTF-8"); '.
                'header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); '.
                'header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); '.
                'header("Cache-Control: no-store, no-cache, must-revalidate"); '.
                'header("Cache-Control: post-check=0, pre-check=0", false); '.
                'header("Pragma: no-cache"); '.
                '%s '.
                '$oContext->close(); '.
                '?>'.
                '<root>%s</root>',
                $header_script,
                $this->getXmlTree($tree[0], $tree, $site_srl, $domain)
            );
            // Create php cache file
            $php_output = $this->getPhpCacheCode($tree[0], $tree, $site_srl, $domain);
            $php_buff = sprintf(
                '<?php '.
                'if(!defined("__KARYBU__")) exit(); '.
                'if(!defined("__KARYBU__")) exit(); '.
                '%s; '.
                '%s; '.
                '$menu = new stdClass();'.
                '$menu->list = array(%s); '.
				'Context::set("included_menu", $menu); '.
                '?>',
                $header_script,
                $php_output['name'],
                $php_output['buff']
            );
            // Save File
            FileHandler::writeFile($xml_file, $xml_buff);
            FileHandler::writeFile($php_file, $php_buff);
            return $xml_file;
        }

		/**
		 * Create xml data recursively looping for array nodes by referencing to parent_srl
		 * menu xml file uses a tag named "node" and this XML configures menus on admin page.
		 * (Implement tree menu by reading the xml file in tree_menu.js)
		 * @param array $source_node
		 * @param array $tree
		 * @param int $site_srl
		 * @param string $domain
		 * @return string
		 */
        function getXmlTree($source_node, $tree, $site_srl, $domain) {
            if(!$source_node) {
                return;
            }
            $buff = '';
            $oMenuAdminModel = &getAdminModel('menu');

            foreach($source_node as $menu_item_srl => $node) {
                $child_buff = "";
                // Get data of the child nodes
                if($menu_item_srl && !empty($tree[$menu_item_srl])) {
                    $child_buff = $this->getXmlTree($tree[$menu_item_srl], $tree, $site_srl, $domain);
                }
                // List variables
                $names = $oMenuAdminModel->getMenuItemNames($node->name, $site_srl);
                $name_arr_str = '';
                foreach($names as $key => $val) {
                    $name_arr_str .= sprintf('"%s"=>"%s",',$key, str_replace('\\','\\\\',htmlspecialchars($val)));
                }
                $name_str = sprintf('$_names = array(%s); print $_names[$lang_type];', $name_arr_str);

                $url = str_replace(array('&','"','<','>'),array('&amp;','&quot;','&lt;','&gt;'),$node->url);
                if(preg_match('/^([0-9a-zA-Z\_\-]+)$/', $node->url)) {
                    $href = getSiteUrl($domain, '','mid',$node->url);
                    $pos = strpos($href, $_SERVER['HTTP_HOST']);
                    if($pos !== false) $href = substr($href, $pos+strlen($_SERVER['HTTP_HOST']));
                } else $href = $url;
                $open_window = $node->open_window;
                $expand = $node->expand;

                $normal_btn = $node->normal_btn;
                if($normal_btn && preg_match('/^\.\/files\/attach\/menu_button/i',$normal_btn)) $normal_btn = str_replace(array('&','"','<','>'),array('&amp;','&quot;','&lt;','&gt;'),$normal_btn);
                else $normal_btn = '';
                $hover_btn = $node->hover_btn;
                if($hover_btn && preg_match('/^\.\/files\/attach\/menu_button/i',$hover_btn)) $hover_btn = str_replace(array('&','"','<','>'),array('&amp;','&quot;','&lt;','&gt;'),$hover_btn);
                else $hover_btn = '';
                $active_btn = $node->active_btn;
                if($active_btn && preg_match('/^\.\/files\/attach\/menu_button/i',$active_btn)) $active_btn = str_replace(array('&','"','<','>'),array('&amp;','&quot;','&lt;','&gt;'),$active_btn);
                else $active_btn = '';

                $group_srls = $node->group_srls;

                if($normal_btn) {
                    if(preg_match('/\.png$/',$normal_btn)) $classname = 'class=&quot;iePngFix&quot;';
                    else $classname = '';
                    if($hover_btn) $hover_str = sprintf('onmouseover=&quot;this.src=\'%s\'&quot;', $hover_btn); else $hover_str = '';
                    if($active_btn) $active_str = sprintf('onmousedown=&quot;this.src=\'%s\'&quot;', $active_btn); else $active_str = '';
                    $link = sprintf('&lt;img src=&quot;%s&quot; onmouseout=&quot;this.src=\'%s\'&quot; alt=&quot;<?php print htmlspecialchars($_names[$lang_type]) ?>&quot; %s %s %s /&gt;', $normal_btn, $normal_btn, $hover_str, $active_str, $classname);
                } else {
                    $link = '<?php print $_names[$lang_type]; ?>';
                }
                // If the value of node->group_srls exists
                if($group_srls)$group_check_code = sprintf('($is_admin==true||(is_array($group_srls)&&count(array_intersect($group_srls, array(%s))))||($is_logged&&%s))',$group_srls,$group_srls == -1?1:0);
                else $group_check_code = "true";
                $attribute = sprintf(
                    'node_srl="%s" parent_srl="%s" text="<?php if(%s) { %s }?>" url="<?php print(%s?"%s":"")?>" href="<?php print(%s?"%s":"")?>" open_window="%s" expand="%s" normal_btn="%s" hover_btn="%s" active_btn="%s" link="<?php if(%s) {?>%s<?php }?>"',
                    $menu_item_srl,
                    $node->parent_srl,
                    $group_check_code,
                    $name_str,
                    $group_check_code,
                    $url,
                    $group_check_code,
                    $href,
                    $open_window,
                    $expand,
                    $normal_btn,
                    $hover_btn,
                    $active_btn,
                    $group_check_code,
                    $link
                );

                if($child_buff) {
                    $buff .= sprintf('<node %s>%s</node>', $attribute, $child_buff);
                }
                else {
                    $buff .=  sprintf('<node %s />', $attribute);
                }
            }
            return $buff;
        }

		/**
		 * Return php code converted from nodes in an array
		 * Although xml data can be used for tpl, menu to menu, it needs to use javascript separately
		 * By creating cache file in php and then you can get menu information without DB
		 * This cache includes in ModuleHandler::displayContent() and then Context::set()
		 * @param array $source_node
		 * @param array $tree
		 * @param int $site_srl
		 * @param string $domain
		 * @return array
		 */
        function getPhpCacheCode($source_node, $tree, $site_srl, $domain) {
            $output = array("buff"=>"", "url_list"=>array());
            if(!$source_node) {
                return $output;
            }

            $oMenuAdminModel = &getAdminModel('menu');

            foreach($source_node as $menu_item_srl => $node) {
                // Get data from child nodes if exist.
                if($menu_item_srl && !empty($tree[$menu_item_srl])) {
                    $child_output = $this->getPhpCacheCode($tree[$menu_item_srl], $tree, $site_srl, $domain);
                }
                else {
                    $child_output = array("buff"=>"", "url_list"=>array());
                }
                // List variables
                $names = $oMenuAdminModel->getMenuItemNames($node->name, $site_srl);
				unset($name_arr_str);
                $name_arr_str = '';
                foreach($names as $key => $val) {
                    $name_arr_str .= sprintf('"%s"=>"%s",',$key, str_replace(array('\\','"'),array('\\\\','&quot;'),$val));
                }
                $name_str = sprintf('$_menu_names[%d] = array(%s); %s',
                    $node->menu_item_srl,
                    $name_arr_str,
                    !empty($child_output['name']) ? $child_output['name'] : ''
                );
                // If url value is not empty in the current node, put the value into an array url_list
                if($node->url) {
                    $child_output['url_list'][] = $node->url;
                }
                $output['url_list'] = array_merge($output['url_list'], $child_output['url_list']);
                // If node->group_srls value exists
                if(!empty($node->group_srls)){
                    $group_check_code = sprintf('($is_admin==true||(is_array($group_srls)&&count(array_intersect($group_srls, array(%s))))||($is_logged && %s))',$node->group_srls,$node->group_srls == -1?1:0);
                }
                else {
                    $group_check_code = "true";
                }
                // List variables
                if (!empty($node->href)) {
                    $href = str_replace(array('&','"','<','>'),array('&amp;','&quot;','&lt;','&gt;'),$node->href);
                }
                else{
                    $href = '';
                }
                if (!empty($node->url)){
                    $url = str_replace(array('&','"','<','>'),array('&amp;','&quot;','&lt;','&gt;'),$node->url);
                    if(preg_match('/^([0-9a-zA-Z\_\-]+)$/i', $node->url)) {
                        $href = getSiteUrl($domain, '','mid',$node->url);
                        $pos = strpos($href, $_SERVER['HTTP_HOST']);
                        if($pos !== false) {
                            $href = substr($href, $pos+strlen($_SERVER['HTTP_HOST']));
                        }
                    }
                    else {
                        $href = $url;
                    }
                }
                else {
                    $url = '';
                }
                $open_window = !empty($node->open_window) ? $node->open_window : null;
                if (!empty($node->normal_btn)) {
                    $normal_btn = str_replace(array('&','"','<','>'),array('&amp;','&quot;','&lt;','&gt;'),$node->normal_btn);
                }
                else {
                    $normal_btn = null;
                }
                if (!empty($node->hover_btn)) {
                    $hover_btn = str_replace(array('&','"','<','>'),array('&amp;','&quot;','&lt;','&gt;'),$node->hover_btn);
                }
                else {
                    $hover_btn = null;
                }
                if (!empty($node->active_btn)) {
                    $active_btn = str_replace(array('&','"','<','>'),array('&amp;','&quot;','&lt;','&gt;'),$node->active_btn);
                }
                else {
                    $active_btn = null;
                }
				
				foreach($child_output['url_list'] as $key =>$val)
				{
					$child_output['url_list'][$key] = addslashes($val);
				}

                $selected = '"'.implode('","',$child_output['url_list']).'"';
                $child_buff = $child_output['buff'];
                $expand = $node->expand;

                $normal_btn = $node->normal_btn;
                if($normal_btn && preg_match('/^\.\/files\/attach\/menu_button/i',$normal_btn)) {
                    $normal_btn = str_replace(array('&','"','<','>'),array('&amp;','&quot;','&lt;','&gt;'),$normal_btn);
                }
                else {
                    $normal_btn = '';
                }

                $hover_btn = !empty($node->hover_btn) ? $node->hover_btn : null;
                if($hover_btn && preg_match('/^\.\/files\/attach\/menu_button/i',$hover_btn)) {
                    $hover_btn = str_replace(array('&','"','<','>'),array('&amp;','&quot;','&lt;','&gt;'),$hover_btn);
                }
                else {
                    $hover_btn = '';
                }

                $active_btn = !empty($node->active_btn) ? $node->active_btn : null;
                if($active_btn && preg_match('/^\.\/files\/attach\/menu_button/i',$active_btn)) {
                    $active_btn = str_replace(array('&','"','<','>'),array('&amp;','&quot;','&lt;','&gt;'),$active_btn);
                }
                else {
                    $active_btn = '';
                }

                $group_srls = !empty($node->group_srls) ? $node->group_srls : null;

                if($normal_btn) {
                    if(preg_match('/\.png$/',$normal_btn)) $classname = 'class=\"iePngFix\"';
                    else $classname = '';
                    if($hover_btn) $hover_str = sprintf('onmouseover=\"this.src=\'%s\'\"', $hover_btn); else $hover_str = '';
                    if($active_btn) $active_str = sprintf('onmousedown=\"this.src=\'%s\'\"', $active_btn); else $active_str = '';
                    $link = sprintf('"<img src=\"%s\" onmouseout=\"this.src=\'%s\'\" alt=\"".$_menu_names[%d][$lang_type]."\" %s %s %s />"', $normal_btn, $normal_btn, $node->menu_item_srl, $hover_str, $active_str, $classname);
                    if($active_btn) $link_active = sprintf('"<img src=\"%s\" alt=\"".$_menu_names[%d][$lang_type]."\" %s />"', $active_btn, $node->menu_item_srl, $classname);
                    else $link_active = $link;
                } else {
                    $link_active = $link = sprintf('$_menu_names[%d][$lang_type]', $node->menu_item_srl);
                }
                // Create properties (check if it belongs to the menu node by url_list. It looks a trick but fast and powerful)
                $attribute = sprintf(
                    '"node_srl"=>"%s","parent_srl"=>"%s","text"=>(%s?$_menu_names[%d][$lang_type]:""),"href"=>(%s?"%s":""),"url"=>(%s?"%s":""),"open_window"=>"%s","normal_btn"=>"%s","hover_btn"=>"%s","active_btn"=>"%s","selected"=>(array(%s)&&in_array(Context::get("mid"),array(%s))?1:0),"expand"=>"%s", "list"=>array(%s),  "link"=>(%s? ( array(%s)&&in_array(Context::get("mid"),array(%s)) ?%s:%s):""),',
                    $node->menu_item_srl,
                    $node->parent_srl,
                    $group_check_code,
                    $node->menu_item_srl,
                    $group_check_code,
                    $href,
                    $group_check_code,
                    $url,
                    $open_window,
                    $normal_btn,
                    $hover_btn,
                    $active_btn,
                    $selected,
                    $selected,
                    $expand,
                    $child_buff,
                    $group_check_code,
                    $selected,
                    $selected,
                    $link_active,
                    $link
                );
                // Generate buff data
                $output['buff'] .=  sprintf('%s=>array(%s),', $node->menu_item_srl, $attribute);
                if (!isset($output['name'])){
                    $output['name'] = '';
                }
                $output['name'] .= $name_str;
            }
            return $output;
        }

		/**
		 * Mapping menu and layout
		 * When setting menu on the layout, map the default layout
		 * @param int $layout_srl
		 * @param array $menu_srl_list
		 */
        function updateMenuLayout($layout_srl, $menu_srl_list) {
            if(!count($menu_srl_list)) return;
            // Delete the value of menu_srls
            $args->menu_srls = implode(',',$menu_srl_list);
            $output = executeQuery('menu.deleteMenuLayout', $args);
            if(!$output->toBool()) return $output;

            $args->layout_srl = $layout_srl;
            // Mapping menu_srls, layout_srl
            for($i=0;$i<count($menu_srl_list);$i++) {
                $args->menu_srl = $menu_srl_list[$i];
                $output = executeQuery('menu.insertMenuLayout', $args);
                if(!$output->toBool()) return $output;
            }
        }

		/**
		 * Register a menu image button
		 * @param object $args
		 * @return array
		 */
        function _uploadButton($args)
		{
			// path setting
			$path = sprintf('./files/attach/menu_button/%d/', $args->menu_srl);
			if(!empty($args->menu_normal_btn) || !empty($args->menu_hover_btn) || !empty($args->menu_active_btn)) {
                if(!is_dir($path)) {
                    FileHandler::makeDir($path);
                }
            }

			if((isset($args->isNormalDelete) && $args->isNormalDelete == 'Y')
                || (isset($args->isHoverDelete) && $args->isHoverDelete == 'Y')
                || (isset($args->isActiveDelete) && $args->isActiveDelete == 'Y'))
			{
				$oMenuModel = &getAdminModel('menu');
            	$itemInfo = $oMenuModel->getMenuItemInfo($args->menu_item_srl);

				if(isset($args->isNormalDelete) && $args->isNormalDelete == 'Y' && !empty($itemInfo->normal_btn)) {
                    FileHandler::removeFile($itemInfo->normal_btn);
                }
				if(isset($args->isHoverDelete) && $args->isHoverDelete == 'Y' && !empty($itemInfo->hover_btn)) {
                    FileHandler::removeFile($itemInfo->hover_btn);
                }
				if(isset($args->isActiveDelete) && $args->isActiveDelete == 'Y' && !empty($itemInfo->active_btn)) {
                    FileHandler::removeFile($itemInfo->active_btn);
                }
			}

			$returnArray = array();
			// normal button
			if(!empty($args->menu_normal_btn))
			{
				$tmp_arr = explode('.',$args->menu_normal_btn['name']);
				$ext = $tmp_arr[count($tmp_arr)-1];

				$filename = sprintf('%s%d.%s.%s', $path, $args->menu_item_srl, 'menu_normal_btn', $ext);
				move_uploaded_file($args->menu_normal_btn['tmp_name'], $filename);
				$returnArray['normal_btn'] = $filename;
			}

			// hover button
			if(!empty($args->menu_hover_btn))
			{
				$tmp_arr = explode('.',$args->menu_hover_btn['name']);
				$ext = $tmp_arr[count($tmp_arr)-1];

				$filename = sprintf('%s%d.%s.%s', $path, $args->menu_item_srl, 'menu_hover_btn', $ext);
				move_uploaded_file($args->menu_hover_btn['tmp_name'], $filename);
				$returnArray['hover_btn'] = $filename;
			}

			// active button
			if(!empty($args->menu_active_btn))
			{
				$tmp_arr = explode('.',$args->menu_active_btn['name']);
				$ext = $tmp_arr[count($tmp_arr)-1];

				$filename = sprintf('%s%d.%s.%s', $path, $args->menu_item_srl, 'menu_active_btn', $ext);
				move_uploaded_file($args->menu_active_btn['tmp_name'], $filename);
				$returnArray['active_btn'] = $filename;
			}
			return $returnArray;
        }
    }
?>
