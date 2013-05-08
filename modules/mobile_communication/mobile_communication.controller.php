<?php
	/**
	 * @class  mobile_communicationController
	 * @author  ()
	 * @brief  Controller for the mobile_communication module
	 **/
	class mobile_communicationController extends mobile_communication {
		/**
		 * @brief Initialization
		 *
		 **/
		function init() {
		}
		
		function logout_message()
		{
			echo "logout_error!";
			exit;
		}	
		
		function procmobile_communicationLogin() 
		{
			
            $user_id = Context::get('user_id');

            $password = Context::get('password');
            $remember = Context::get('remember');
            $oMemberController = getController('member');
            
            if( $remember == 'Y' )
        	{	
            $output = $oMemberController->doLogin($user_id, $password,true);
            }
            else $output = $oMemberController->doLogin($user_id, $password, false);
            
			if (!$output->toBool()) {
                header('Content-Type: text/xml');
                echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
                echo "<response>\n";
                echo "<value>false</value>";
                echo "</response>\n";
            }else{
                header('Content-Type: text/xml');
                echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
                echo "<response>\n";
                echo "<value>true</value>";
                echo "</response>\n";
            }
            exit;
		}

        function procmobile_communicationLogout() 
        {
            $oMemberController = getController('member');
            $output = $oMemberController->procMemberLogout();
            if (!$output->toBool()) {
                header('Content-Type: text/xml');
                echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
                echo "<response>\n";
                echo "<value>false</value>";
                echo "</response>\n";
            }else{
                header('Content-Type: text/xml');
                echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
                echo "<response>\n";
                echo "<value>true</value>";
                echo "</response>\n";
            }
            exit;
        }
        function procmobile_communicationArrangeMenu(){
        $adminMenuController = getAdminController("menu");
        $adminMenuController->menuSrl = Context::get('menu_srl');
        
        $parentKeyList = Context::get('parent_key');
        $adminMenuController->itemKeyList = Context::get('item_key');
        $adminMenuController->map = array();
        if (is_array($parentKeyList)) {
            foreach ($parentKeyList as $no => $srl) {
                if ($srl === 0)
                    continue;
                if (!is_array($adminMenuController->map[$srl]))
                    $adminMenuController->map[$srl] = array();
                $adminMenuController->map[$srl][] = $no;
            }
        }

        $result = array();
        if (is_array($adminMenuController->itemKeyList)) {
            foreach ($adminMenuController->itemKeyList as $srl) {
                if (!$adminMenuController->checked[$srl]) {
                    unset($target);
                    $adminMenuController->checked[$srl] = 1;
                    $target->node = $srl;
                    $target->child = array();

                    while (count($adminMenuController->map[$srl])) {
                        $adminMenuController->_setParent($srl, array_shift($adminMenuController->map[$srl]), $target);
                    }
                    $result[] = $target;
                }
            }
        }

        if (is_array($result)) {
            $i = 0;
            foreach ($result AS $key => $node) {
                $adminMenuController->moveMenuItem($adminMenuController->menuSrl, 0, $i, $node->node, 'move'); //move parent node
                $adminMenuController->_recursiveMoveMenuItem($node); //move child node
                $i = $node->node;
            }
        }

        header('Content-Type: text/xml');
                echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
                echo "<response>\n";
                echo "<value>true</value>";
                echo "</response>\n";
        }
        function procmobile_communicationDisplayMenu()
        {
        	if(!Context::get('is_logged')) $this->logout_message();
			$oMenuAdminModel = getAdminModel('menu');
			$menuListFromDB = $oMenuAdminModel->getMenus();
			if(is_array($menuListFromDB)) $output = array_reverse($menuListFromDB);

			$menuList = array();
			if(is_array($output))
			{
				$menuItems = array();
				foreach($output AS $key=>$value)
				{
					if($value->title == '__XE_ADMIN__') unset($output[$key]);
					else
					{
						unset($menu);
						unset($menuItems);
						$value->php_file = sprintf('./files/cache/menu/%s.php',$value->menu_srl);
						if(file_exists($value->php_file)) @include($value->php_file);

						
						//array_push($menuList, $value->xml_file);
						$menuItems->menuSrl = $value->menu_srl;
						$menuItems->title = $value->title;
						$menuItems->menuItems = $menu;
						array_push($menuList, $menuItems);
					}
				}
			}
			
			header('Content-Type: text/xml');
        	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>";
			//should create multiple levels of menu
			foreach($menuList as $value)
				{
					echo "<menu>\n";
					echo "<menuName>" . $value->title . "</menuName>";
					echo "<menuSrl>" . $value->menuSrl . "</menuSrl>";
					
					if( !empty($value->menuItems->list) ){
                                            echo $this->generateMenuItemMultipleLevels ($value->menuItems->list);
                                        }
	
					echo "</menu>\n";
				}
			echo "</response>";
                      
			exit;
        }
        
        private function generateMenuItemMultipleLevels($list){
            $xml = "";
            if(!empty($list)){
                foreach( $list as $item ){                
                    $xml .= "<menuItem>";
                    $xml .= "<menuItemName>" . $item["text"] . "</menuItemName>";
                    $xml .= "<srl>" . $item["node_srl"] . "</srl>";
                    $xml .= "<open_window>" . $item["open_window"] . "</open_window>";
                    $xml .= "<url>" . $item["url"] . "</url>";
                    $xml .= $this->generateMenuItemMultipleLevels($item["list"]);
                    $xml .= "</menuItem>";
                }
                
                }
            return $xml;
        }
        
        function procmobile_communicationDisplayPages()
        {
            if(!Context::get('is_logged')) $this->logout_message();
        	$args->sort_index = "module_srl";
            $args->page = Context::get('page');
            $args->list_count = 40;
            $args->page_count = 10;
            $args->s_module_category_srl = Context::get('module_category_srl');

			$s_mid = Context::get('s_mid');
			if($s_mid) $args->s_mid = $s_mid;

			$s_browser_title = Context::get('s_browser_title');
			if($s_browser_title) $args->s_browser_title = $s_browser_title;

            $output = executeQuery('page.getPageList', $args);
			$oModuleModel = getModel('module');
			$page_list = $oModuleModel->addModuleExtraVars($output->data);
            moduleModel::syncModuleToSite($page_list);
            
            header('Content-Type: text/xml');
        	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>\n";
			
			foreach($page_list as $value)
        	{
        			//var_dump($value);
        			echo "<page>";
        			echo "<module_srl>" . $value->module_srl . "</module_srl>";
        			echo "<module>" . $value->module . "</module>";
        			echo "<page_type>" . $value->page_type . "</page_type>";
        			echo "<mid>". $value->mid . "</mid>";
        			echo "<content><![CDATA[" . $value->content . "]]></content>";
        			echo "<document_srl>" . $value->document_srl . "</document_srl>";
        			echo "<browser_title>" . $value->browser_title . "</browser_title>";                                
        			echo "<layout_srl>" . $value->layout_srl . "</layout_srl>";
                                // Get virtual site
                                $virtual_site="";
                                if($value->site_srl!=0){
                                    $result = executeQuery('module.getSite', $value);
                                    $virtual_site=$result->data->domain;
                                }
                                echo "<virtual_site>". $virtual_site ."</virtual_site>";
        			echo "</page>";
        	}
        	echo "</response>\n";
        	exit;
        }
        
        function procmobile_communicationDisplayMembers()
        {
        if(!Context::get('is_logged')) $this->logout_message();
        	$oMemberAdminModel = getAdminModel('member');
            $oMemberModel = getModel('member');
            $output = $oMemberAdminModel->getMemberList();
            
            header('Content-Type: text/xml');
        	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>\n";
			//var_dump($output->data);
            foreach($output->data as $user)
            {
            	echo "<user>\n";
            	echo "<nickname>" . $user->nick_name . "</nickname>";
            	echo "<password>" .$user->password . "</password>";
            	echo "<member_srl>" .$user->member_srl . "</member_srl>";
            	echo "<denied>" . $user->denied . "</denied>";
            	echo "<user_id>" . $user->user_id . "</user_id>";
            	echo "<email>" . $user->email_address . "</email>";
            	echo "<allow_mailing>" . $user->allow_mailing . "</allow_mailing>";
            	echo "<allow_message>" . $user->allow_message . "</allow_message>";
            	echo "<description>" . $user->description . "</description>";
            	echo "<find_account_question>" . $user->find_account_question . "</find_account_question>";
            	echo "<secret_answer>" .$user->find_account_answer ."</secret_answer>";
            	echo "<is_admin>" .$user->is_admin . "</is_admin>";
            	echo "</user>\n";
            }
            
            echo "</response>\n";
        	exit;
        }
        
        function procmobile_communicationEditMember()
        {
        if(!Context::get('is_logged')) $this->logout_message();
        	$args = Context::gets('member_srl','email_address','find_account_answer', 'allow_mailing','allow_message','denied','is_admin','description','group_srl_list','limit_date');
            $oMemberModel = &getModel ('member');
            $config = $oMemberModel->getMemberConfig ();
			$getVars = array();
			if ($config->signupForm){
				foreach($config->signupForm as $formInfo){
					if($formInfo->isDefaultForm && ($formInfo->isUse || $formInfo->required || $formInfo->mustRequired)){
						$getVars[] = $formInfo->name;
					}
				}
			}
			foreach($getVars as $val){
				$args->{$val} = Context::get($val);
			}
			$args->member_srl = Context::get('member_srl');
			if (Context::get('reset_password'))
				$args->password = Context::get('reset_password');
			else unset($args->password);

			// Remove some unnecessary variables from all the vars
			$all_args = Context::getRequestVars();
			unset($all_args->module);
			unset($all_args->act);
			unset($all_args->mid);
			unset($all_args->error_return_url);
			unset($all_args->success_return_url);
			unset($all_args->ruleset);
			if(!isset($args->limit_date)) $args->limit_date = "";
			// Add extra vars after excluding necessary information from all the requested arguments
			$extra_vars = delObjectVars($all_args, $args);
			$args->extra_vars = serialize($extra_vars);
			// Check if an original member exists having the member_srl
			if($args->member_srl) {
				// Create a member model object
				$oMemberModel = &getModel('member');
				// Get memebr profile
				$columnList = array('member_srl');
				$member_info = $oMemberModel->getMemberInfoByMemberSrl($args->member_srl, 0, $columnList);
				// If no original member exists, make a new one
				if($member_info->member_srl != $args->member_srl) unset($args->member_srl);
			}

			// remove whitespace
			$checkInfos = array('user_id', 'nick_name', 'email_address');
			$replaceStr = array("\r\n", "\r", "\n", " ", "\t", "\xC2\xAD");
			foreach($checkInfos as $val){
				if(isset($args->{$val})){
					$args->{$val} = str_replace($replaceStr, '', $args->{$val});
				}
			}

			$oMemberController = &getController('member');
			// Execute insert or update depending on the value of member_srl
			if(!$args->member_srl) {
				$args->password = Context::get('password');
				$output = $oMemberController->insertMember($args);
				$msg_code = 'success_registed';
			} else {
				$output = $oMemberController->updateMember($args);
				$msg_code = 'success_updated';
			}

			if(!$output->toBool()) 
			{
			header('Content-Type: text/xml');
        	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>\n";
			echo "<value>false</value>";
			echo "</response>";		
			exit;
			}
			// Save Signature
			
			
			$signature = Context::get('signature');
			$oMemberController->putSignature($args->member_srl, $signature);
			// Return result
			
			$member = &getAdminController('member');
			
			$member->add('member_srl', $args->member_srl);
			$member->setMessage($msg_code);

			$profile_image = $_FILES['profile_image'];
			if (is_uploaded_file($profile_image['tmp_name'])){
				$oMemberController->insertProfileImage($args->member_srl, $profile_image['tmp_name']);
			}

			$image_mark = $_FILES['image_mark'];
			if (is_uploaded_file($image_mark['tmp_name'])){
				$oMemberController->insertImageMark($args->member_srl, $image_mark['tmp_name']);
			}

			$image_name = $_FILES['image_name'];
			if (is_uploaded_file($image_name['tmp_name'])){
				$oMemberController->insertImageName($args->member_srl, $image_name['tmp_name']);
			}
			
			header('Content-Type: text/xml');
        	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>\n";
			echo "<value>true</value>";
			echo "</response>";		
			exit;
        }
        
        function procmobile_communicationCheckTextyleAndForum()
        {
        	if(!Context::get('is_logged')) $this->logout_message();
        	$oAdminModel = getAdminModel('admin');
			$oModuleModel = getModel('module');
			$oAutoinstallModel = getModel('autoinstall');

			$module_list = $oModuleModel->getModuleList();
			
			$checkForum = 0;
			$checkTextyle = 0;
			foreach($module_list as $value)
			{
			   if( $value->title == "Textyle") $checkTextyle = 1;
			   if( $value->title == "Forum") $checkForum = 1;
			}
			header('Content-Type: text/xml');
        	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>";
			echo "<forum>";
			if($checkForum) echo "yes";
					else echo "no";
			echo "</forum>";
			echo "<textyle>";
			if($checkTextyle) echo "yes";
					else echo "no";
			echo "</textyle>";
			echo "</response>\n";
			exit;
		}
		
		function procmobile_communicationDeleteMenuItem()
		{
		if(!Context::get('is_logged')) $this->logout_message();
			$args = Context::gets('menu_srl','menu_item_srl');

            $oMenuAdminModel = &getAdminModel('menu');
			$menuController = &getAdminController('menu');
			
            // Get information of the menu
            $menu_info = $oMenuAdminModel->getMenu($args->menu_srl);
            $menu_title = $menu_info->title;

            // Get original information
            $item_info = $oMenuAdminModel->getMenuItemInfo($args->menu_item_srl);

			if($menu_title == '__XE_ADMIN__' && $item_info->parent_srl == 0)return $this->stop('msg_cannot_delete_for_admin_topmenu');

            if($item_info->parent_srl) $parent_srl = $item_info->parent_srl;
            // Display an error that the category cannot be deleted if it has a child node
            $output = executeQuery('menu.getChildMenuCount', $args);
            if(!$output->toBool()) 
            {
            header('Content-Type: text/xml');
        	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>";
			echo "<value>false</value>";
			echo "</response>";
			exit;
            }
            if($output->data->count>0) 
            	{
            header('Content-Type: text/xml');
        	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>";
			echo "<value>copil</value>";
			echo "</response>";
			exit;
            	}
            // Remove from the DB
            $output = executeQuery("menu.deleteMenuItem", $args);
            if(!$output->toBool()) return $output;
            // Update the xml file and get its location
            $xml_file = $menuController->makeXmlFile($args->menu_srl);
            // Delete all of image buttons
            if($item_info->normal_btn) FileHandler::removeFile($item_info->normal_btn);
            if($item_info->hover_btn) FileHandler::removeFile($item_info->hover_btn);
            if($item_info->active_btn) FileHandler::removeFile($item_info->active_btn);

            $this->add('xml_file', $xml_file);
            $this->add('menu_title', $menu_title);
            $this->add('menu_item_srl', $parent_srl);
            $this->setMessage('success_deleted');
            
            header('Content-Type: text/xml');
        	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>";
			echo "<value>true</value>";
			echo "</response>";
			exit;
		}
		
		function procmobile_communicationMenuDelete() 
		{
            if(!Context::get('is_logged')) $this->logout_message();
            
            $menu_srl = Context::get('menu_srl');

			$oMenuAdminModel = getAdminModel('menu');
			$menu_info = $oMenuAdminModel->getMenu($menu_srl);

			if($menu_info->title == '__XE_ADMIN__')
				return new Object(-1, 'msg_adminmenu_cannot_delete');

            $this->deleteMenu($menu_srl);

			$this->setMessage('success_deleted', 'info');
			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
				$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMenuAdminSiteMap');
				$this->setRedirectUrl($returnUrl);
				return;
			}
        }

		//problema la ce returneaza
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

            $args->menu_srl = $menu_srl;
            // Delete menu items
            $output = executeQuery("menu.deleteMenuItems", $args);
            if(!$output->toBool()) return $output;
            // Delete the menu
            $output = executeQuery("menu.deleteMenu", $args);
            if(!$output->toBool()) return $output;

            return new Object(0,'success_deleted');
        }
        
        //problema la ce returneaza
        function procmobile_communicationMenuInsert()
        	{
        	if(!Context::get('is_logged')) $this->logout_message();
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

			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) 
				{
				$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMenuAdminContent');
				$this->setRedirectUrl($returnUrl);
				return;
				}
        	}
        	
        	function procmobile_communicationPageInsert() 
        	{
        	if(!Context::get('is_logged')) $this->logout_message();
            // Create model/controller object of the module module
            $oModuleController = getController('module');
            $oModuleModel = getModel('module');
            // Set board module
            $args = Context::getRequestVars();
            $args->module = 'page';
            $args->mid = $args->page_name;	//because if mid is empty in context, set start page mid
			$args->path = (!$args->path) ? '' : $args->path;
			$args->mpath = (!$args->mpath) ? '' : $args->mpath;
            unset($args->page_name);

			if($args->use_mobile != 'Y') $args->use_mobile = '';
            // Check if an original module exists by using module_srl
            if($args->module_srl) 
            {
				$columnList = array('module_srl');
                $module_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl, $columnList);
                if($module_info->module_srl != $args->module_srl) 
                	{
					unset($args->module_srl);
					}
				else
				{
					foreach($args as $key=>$val)
					{
						$module_info->{$key} = $val;
					}
					$args = $module_info;
				}
            }

			switch ($args->page_type){
				case 'WIDGET' : {
									unset($args->skin);
									unset($args->mskin);
									unset($args->path);
									unset($args->mpath);
									break;
								}
				case 'ARTICLE' : {
									unset($args->page_caching_interval);
									unset($args->path);
									unset($args->mpath);
									break;
								}
				case 'OUTSIDE' : {
									unset($args->skin);
									unset($args->mskin);
									break;
								}
			}
            // Insert/update depending on module_srl
            if(!$args->module_srl) {
                $output = $oModuleController->insertModule($args);
                $msg_code = 'success_registed';
            } 
            else {
                $output = $oModuleController->updateModule($args);
                $msg_code = 'success_updated';
            }
			
			header('Content-Type: text/xml');
        	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>\n";
            if(!$output->toBool()) 
            	{
            		echo "<value>" . "false" . "</value>";
            	}
            	else
            	{
            		echo "<value>" . "true" . "</value>";
            	}
            echo "</response>";
            exit;
        }
        
        function procmobile_communicationListModules()
        {
           if(!Context::get('is_logged')) $this->logout_message();
            
			$from = Context::get('from');

			$oModuleController = getController('module');
            $oModuleModel = getModel('module');
            // Variable setting for site keyword
            $site_keyword = Context::get('site_keyword');
            $site_srl = Context::get('site_srl');
            // If there is no site keyword, use as information of the current virtual site
            $args = null;

            // Get a list of modules at the site
			$args->module = array();
			if($from == 'document')
			{
				array_push($args->module, 'bodex');
				array_push($args->module, 'beluxe');
			}
            // before trigger
            $output = ModuleHandler::triggerCall('module.procModuleAdminGetList', 'before', $args->module);
            if(!$output->toBool()) return $output;

            $logged_info = Context::get('logged_info');
			$site_module_info = Context::get('site_module_info');
			if($site_keyword) $args->site_keyword = $site_keyword;

			if(!$site_srl)
			{
				if($logged_info->is_admin == 'Y' && !$site_keyword) $args->site_srl = 0;
				else $args->site_srl = (int)$site_module_info->site_srl;
			}
			else $args->site_srl = $site_srl;

			$args->sort_index1 = 'sites.domain';

            // Get a list of modules at the site
            //$output = executeQueryArray('module.getSiteModules', $args);
              $output = executeQueryArray('mobile_communication.getSitePages', $args);
            
           // var_dump($output);
            
            header('Content-Type: text/xml');
        	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>\n";
			
			foreach($output->data as $value)
			{
				echo "<newmodule>";
				echo "<module>" . $value->mid . "</module>";
				echo "<module_srl>" . $value->module_srl . "</module_srl>";
                                echo "<page_type>" . $value->page_type . "</page_type>";
				echo "</newmodule>";
			}
			echo "</response>";
			exit;
         	   
        }
        
        function procmobile_communicationMenuItem()
        {
        if(!Context::get('is_logged')) $this->logout_message();
        
        $menuController = &getAdminController('menu');
        $source_args = Context::getRequestVars();
            unset($source_args->module);
            unset($source_args->act);
            if($source_args->menu_open_window!="Y") $source_args->menu_open_window = "N";
            if($source_args->menu_expand !="Y") $source_args->menu_expand = "N";
            if(!is_array($source_args->group_srls)) $source_args->group_srls = str_replace('|@|',',',$source_args->group_srls);
			else $source_args->group_srls = implode(',', $source_args->group_srls);
            $source_args->parent_srl = (int)$source_args->parent_srl;

			if($source_args->cType == 'CREATE') $source_args->menu_url = $source_args->create_menu_url;
			else if($source_args->cType == 'SELECT') $source_args->menu_url = $source_args->select_menu_url;

			// upload button
			$btnOutput = $menuController->_uploadButton($source_args);

            // Re-order variables (Column's order is different between form and DB)
            $args->menu_srl = $source_args->menu_srl;
            $args->menu_item_srl = $source_args->menu_item_srl;
            $args->parent_srl = $source_args->parent_srl;
            $args->menu_srl = $source_args->menu_srl;
            $args->menu_id = $source_args->menu_id;

			if ($source_args->menu_name_key)
	            $args->name = $source_args->menu_name_key;
			else
				$args->name = $source_args->menu_name;

            $args->url = trim($source_args->menu_url);
            $args->open_window = $source_args->menu_open_window;
            $args->expand = $source_args->menu_expand;
            if($btnOutput['normal_btn']) $args->normal_btn = $btnOutput['normal_btn'];
            if($btnOutput['hover_btn']) $args->hover_btn = $btnOutput['hover_btn'];
            if($btnOutput['active_btn']) $args->active_btn = $btnOutput['active_btn'];
            $args->group_srls = $source_args->group_srls;

			// if cType is CREATE, create module
			if($source_args->cType == 'CREATE')
			{
				$site_module_info = Context::get('site_module_info');
				$cmArgs->site_srl = (int)$site_module_info->site_srl;
				$cmArgs->mid = $source_args->create_menu_url;
				$cmArgs->browser_title = $args->name;

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

				$cmArgs->menu_srl = $source_args->menu_srl;
                $oModuleController = getController('module');
				$output = $oModuleController->insertModule($cmArgs);
				if(!$output->toBool()) 
					{
					header('Content-Type: text/xml');
        		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
				echo "<response>\n";
				echo "<value>false</value>";
				echo "</response>";
				exit;
					}
			}

            // Check if already exists
            $oMenuModel = getAdminModel('menu');
            $item_info = $oMenuModel->getMenuItemInfo($args->menu_item_srl);

			// button is deleted, db delete
			if($source_args->isNormalDelete == 'Y') $args->normal_btn = '';
			if($source_args->isHoverDelete == 'Y') $args->hover_btn = '';
			if($source_args->isActiveDelete == 'Y') $args->active_btn = '';

			$message = '';
            // Update if exists
            if(!empty($args->menu_item_srl) && $item_info->menu_item_srl == $args->menu_item_srl) {
                $output = executeQuery('menu.updateMenuItem', $args);
                if(!$output->toBool()) 
            		{
					header('Content-Type: text/xml');
        		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
				echo "<response>\n";
				echo "<value>false</value>";
				echo "</response>";
				exit;
					} 
					else
					{
					header('Content-Type: text/xml');
        		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
				echo "<response>\n";
				echo "<value>true</value>";
				echo "</response>";
					}
            
				$message = 'success_updated';
            // Insert if not exist
            } else {
				if(!$args->menu_item_srl) $args->menu_item_srl = getNextSequence();
                $args->listorder = -1*$args->menu_item_srl;
                $output = executeQuery('menu.insertMenuItem', $args);
                if(!$output->toBool()) 
                {
					header('Content-Type: text/xml');
        		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
				echo "<response>\n";
				echo "<value>false</value>";
				echo "</response>";
				exit;
					} 
					else
					{
					header('Content-Type: text/xml');
        		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
				echo "<response>\n";
				echo "<value>true</value>";
				echo "</response>";
					}
				$message = 'success_registed';
            }
            // Get information of the menu
            $menu_info = $oMenuModel->getMenu($args->menu_srl);
            $menu_title = $menu_info->title;
            
            
            $xml_file = $menuController->makeXmlFile($args->menu_srl);
            
            // Update the xml file and get its location
           // $xml_file = $this->makeXmlFile($args->menu_srl);
            // If a new menu item that mid is URL is added, the current layout is applied
            if(preg_match('/^([a-zA-Z0-9\_\-]+)$/', $args->url)) {
                $mid = $args->url;

                $mid_args->menu_srl = $args->menu_srl;
                $mid_args->mid = $mid;
                // Get layout value of menu_srl
                $output = executeQuery('menu.getMenuLayout', $args);
                // Set if layout value is not specified in the module
                $oModuleModel = getModel('module');
				$columnList = array('layout_srl');
                $module_info = $oModuleModel->getModuleInfoByMid($mid, 0, $columnList);
                if(!$module_info->layout_srl&&$output->data->layout_srl) $mid_args->layout_srl = $output->data->layout_srl;
                // Change menu value of the mid to the menu
                $oModuleController = getController('module');
                $oModuleController->updateModuleMenu($mid_args);
            }

            $this->add('xml_file', $xml_file);
            $this->add('menu_srl', $args->menu_srl);
            $this->add('menu_item_srl', $args->menu_item_srl);
            $this->add('menu_title', $menu_title);
            $this->add('parent_srl', $args->parent_srl);

			exit;
		}

        function procmobile_communicationLoadSettings() 
		{
		if(!Context::get('is_logged')) $this->logout_message();
		Context::loadLang('modules/install/lang');

		$db_info = Context::getDBInfo();
		$lang_selected = Context::loadLangSelected();
		//var_dump($lang_selected);
		
		header('Content-Type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
		echo "<response>" . "\n";
		$langs = "";
		foreach($lang_selected as $key => $value)
			{
				$langs = $langs . $key .":";
			} 
		echo "<langs>" . $langs . "</langs>";
		echo "<default_lang>" . $db_info->lang_type . "</default_lang>";
		echo "<timezone>" . $db_info->time_zone . "</timezone>";
		echo "<mobile>" . $db_info->use_mobile_view . "</mobile>";
		echo "<ips>". $db_info->admin_ip_list . "</ips>";
		echo "<default_url>" . $db_info->default_url . "</default_url>";
		echo "<use_ssl>" . $db_info->use_ssl . "</use_ssl>";
		echo "<rewrite_mode>" . $db_info->use_rewrite . "</rewrite_mode>";
		echo "<use_sso>" . $db_info->use_sso . "</use_sso>";
		echo "<db_session>" .$db_info->use_db_session . "</db_session>";
		echo "<qmail>" . $db_info->qmail_compatibility . "</qmail>";
		echo "<html5>" . $db_info->use_html5 . "</html5>";
		echo "</response>";
		exit;
		}
		
		function procmobile_communicationArticleContent()
		{
		 if(!Context::get('is_logged')) $this->logout_message();
			$document_srl = Context::get('srl');
			$oDocumentModel = getModel('document');
			$oDocument = $oDocumentModel->getDocument($document_srl, true);
			
			echo $oDocument->variables['content'];
			exit;
		}
		
		function procmobile_communicationArticleTitle()
		{
		if(!Context::get('is_logged')) $this->logout_message();
			$document_srl = Context::get('srl');
			$oDocumentModel = getModel('document');
			$oDocument = $oDocumentModel->getDocument($document_srl, true);
			
			echo $oDocument->variables['title'];
			exit;
		}
		
                function getCommentCountOfATextyle($module_srl){
                    $args->module_srl = $module_srl;
                    $output = executeQuery("mobile_communication.getCommentCountOfTextyle",$args);
                    return $output->data->count;
                }
                
		function procmobile_communicationTextyleList() 
		{
		if(!Context::get('is_logged')) $this->logout_message();
            $vars = Context::getRequestVars();
            $oTextyleModel = &getModel('textyle');
			//var_dump($oTextyleModel);
            $page = Context::get('page');
            if(!$page) $page = 1;

            if($vars->search_target && $vars->search_keyword) {
                $args->{'s_'.$vars->search_target} = strtolower($vars->search_keyword);
            }

            $args->list_count = 20;
            $args->page = $page;
            $args->list_order = 'regdate';
            $output = $oTextyleModel->getTextyleList($args);

            
            $oDocument = getModel("document");
            
            if(!$output->toBool()) return $output;

		header('Content-Type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
		echo "<textyle-list>" . "\n";
			//var_dump($output->data);
			foreach( $output->data as $textyle )
			{
			echo "<textyle>";
			echo "<domain>" .$textyle->domain . "</domain>";
			echo "<textyle_srl>" . $textyle->textyle_srl ."</textyle_srl>";
			echo "<module_srl>" . $textyle->module_srl . "</module_srl>";
			echo "<timezone>" . $textyle->timezone . "</timezone>";
			$variables = $textyle->variables;
			echo "<default_lang>". $variables['default_language'] . "</default_lang>";
			echo "<user_id>" . $variables['user_id'] . "</user_id>";
			echo "<site_srl>" . $variables['site_srl'] . "</site_srl>";
			echo "<email_address>" . $variables['email_address'] . "</email_address>";
			echo "<use_mobile>" . $variables['use_mobile'] . "</use_mobile>";
			echo "<mid>" . $variables['mid'] . "</mid>";
			echo "<skin>" . $variables['skin'] . "</skin>";
			echo "<browser_title>" . $variables['browser_title'] . "</browser_title>";
			echo "<textyle_title>" . $variables['textyle_title'] . "</textyle_title>";
                        echo "<comment_count>".$this->getCommentCountOfATextyle($textyle->module_srl)."</comment_count>";
 			echo "</textyle>";
 			}
			echo "</textyle-list>";
			exit;
        }
        
        function procmobile_communicationTextylePostList()
        {
        if(!Context::get('is_logged')) $this->logout_message();
            $args->module_srl = Context::get('module_srl');
            $args->page=  Context::get('page');
            
            $published = Context::get('published');
            $logged_info = Context::get('logged_info');
            
	    

            if(!$published){
                $args->module_srl = array($args->module_srl,$args->module_srl * -1,$logged_info->member_srl);
            }else if($published > 0)
            {
                $args->module_srl = array($args->module_srl,$args->module_srl * -1);
            }else{
                $args->module_srl = $logged_info->member_srl;
            }

            $oDocumentModel = &getModel('document');
            
            $output = $oDocumentModel->getDocumentList($args);

            header('Content-Type: text/xml');
			echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>" . "\n";
                        
                        echo "<pagination>\n";
                        echo "<total_count>".$output->page_navigation->total_count."</total_count>\n";
                        echo "<total_page>".$output->page_navigation->total_page."</total_page>\n";
                        echo "<cur_page>".$output->page_navigation->cur_page."</cur_page>\n";
                        echo "<page_count>".$output->page_navigation->page_count."</page_count>\n";
                        echo "<first_page>".$output->page_navigation->first_page."</first_page>\n";
                        echo "<last_page>".$output->page_navigation->last_page."</last_page>\n";
                        echo "</pagination>\n";

            foreach($output->data as $post)
            {
                $variables = $post->variables;
                if($variables['module_srl']==$logged_info->member_srl){
                    $url=  getUrl('act','dispTextyleToolPostManageWrite','document_srl',$post->document_srl);
                    $status="DRAFT";
                }
                elseif($variables['module_srl']<=0){
                    $url=getUrl('act','dispTextyleToolPostManageWrite','document_srl',$post->document_srl);
                    $status="TRASH";
                }
                else{
                    $url=getUrl('','document_srl',$post->document_srl);
                    $status="PUBLISHED";
                }
                        
            	echo "<post>";
            	echo "<document_srl>" . $post->document_srl . "</document_srl>";
            	echo "<module_srl>" . $variables['module_srl'] . "</module_srl>";
                echo "<comment_count>" . $variables['comment_count'] . "</comment_count>";
                echo "<status>" . $status . "</status>";
                echo "<url>" . $url . "</url>";
            	echo "<category_srl>" . $variables['category_srl'] . "</category_srl>";
            	echo "<title>" . $variables['title'] . "</title>";
            	echo "</post>";
            }
            echo "</response>";
            exit;
         }
//        function procmobile_communicationCommentCount(){
//            
//            
//            getCommentCount($document_srl)
//        }
	function procmobile_communicationContentForPost()
	{
	if(!Context::get('is_logged')) $this->logout_message();
	    $args->module_srl = Context::get('module_srl');
	    $document_srl = Context::get('document_srl');            

            $published = Context::get('published');
            $logged_info = Context::get('logged_info');

            if(!$published){
                $args->module_srl = array($args->module_srl,$args->module_srl * -1,$logged_info->member_srl);
            }else if($published > 0)
            {
                $args->module_srl = array($args->module_srl,$args->module_srl * -1);
            }else{
                $args->module_srl = $logged_info->member_srl;
            }

            $oDocumentModel = &getModel('document');
            
            $output = $oDocumentModel->getDocumentList($args);
	    //var_dump($output->data);
	    foreach($output->data as $value)
		{
			if ( $value->document_srl == $document_srl )
			{
                                $alias = $oDocumentModel->getAlias($document_srl);
                                $variables = $value->variables;
                                header('Content-Type: text/xml');
                                echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
                                echo "<document>";
                                echo "<alias>".$alias."</alias>";
				echo "<content>".  base64_encode($variables['content'])."</content>";
                                echo "</document>";
                                exit;
            
			}
		}
	    
	}
		function procmobile_communicationShowComments()
		{
		if(!Context::get('is_logged')) $this->logout_message();
	        Context::addJsFilter($this->module_path.'tpl/filter', 'insert_denylist.xml');

            // 목록을 구하기 위한 옵션
            $args->page = Context::get('page'); ///< 페이지
            $args->search_keyword = Context::get('search_keyword');
            $args->search_target = Context::get('search_target');

            $args->list_count = 30; ///< 한페이지에 보여줄 글 수
            $args->page_count = 10; ///< 페이지 네비게이션에 나타날 페이지의 수

            $args->sort_index = 'list_order'; ///< 소팅 값

            $args->module_srl = Context::get('module_srl');

            $oCommentModel = &getModel('comment');
            $output = $oCommentModel->getTotalCommentList($args);
            
           // var_dump($output);
            header('Content-Type: text/xml');
			echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>" . "\n";
            
            foreach($output->data as $comment)
            {
            	$comment_dets = $comment->variables;
            	echo "<comment>";
            	echo "<comment_srl>" . $comment_dets['comment_srl'] . "</comment_srl>";
            	echo "<module_srl>" .$comment_dets['module_srl'] ."</module_srl>";
            	echo "<document_srl>" . $comment_dets['document_srl'] ."</document_srl>";
            	echo "<parent_srl>" . $comment_dets['parent_srl'] . "</parent_srl>";
            	echo "<is_secret>" . $comment_dets['is_secret'] . "</is_secret>";
            	echo "<content><![CDATA[" . $comment_dets['content'] . "]]></content>";
            	echo "<regdate>" . $comment_dets['regdate'] . "</regdate>";
            	echo "<nickname>" . $comment_dets['nick_name'] . "</nickname>";
            	echo "<email>" . $comments_dets['email_address'] . "</email>";
            	echo "<homepage>" . $comments_dets['homepage'] . "</homepage>";
            	echo "<ipaddress>" . $comments_dets['ipaddress'] . "</ipaddress>";
            	echo "</comment>";
            }
            echo "</response>";
            exit;
        }
        
        
        function procmobile_communicationTextyleStats()
        {
        if(!Context::get('is_logged')) $this->logout_message();
        	global $lang;
			//if(!Context::get('is_logged')) $this->logout_message();
            // 정해진 일자가 없으면 오늘자로 설정
            $selected_date = Context::get('selected_date');
            if(!$selected_date) $selected_date = date("Ymd");
            Context::set('selected_date', $selected_date);

            // counter model 객체 생성
            $oCounterModel = &getModel('counter');

            // 시간, 일, 월, 년도별로 데이터 가져오기
            $type = Context::get('type');
            if(!$type) {
                $type = 'day';
                Context::set('type',$type);
            }

            $site_srl = Context::get('site_srl');

            $xml->item = array();
            $xml->value = array(array(),array());
            $selected_count = 0;

            // total & today
            $counter = $oCounterModel->getStatus(array(0,date("Ymd")),$site_srl);
            $total->total = $counter[0]->unique_visitor;
            $total->today = $counter[date("Ymd")]->unique_visitor;
            
             $xml->selected_title = Context::getLang('this_week');
                        $xml->last_title = Context::getLang('last_week');

                        $before_url = getUrl('selected_date', date("Ymd",strtotime($selected_date)-60*60*24*7));
                        $after_url = getUrl('selected_date', date("Ymd",strtotime($selected_date)+60*60*24*7));
                        $disp_selected_date = date("Y.m.d", strtotime($selected_date));
                        $detail_status = $oCounterModel->getHourlyStatus('week', $selected_date, $site_srl);
                        foreach($detail_status->list as $key => $val) {
                            $_k = date("Y.m.d", strtotime($key)).'('.$lang->unit_week[date('l',strtotime($key))].')';
                            if($selected_date == date("Ymd")&&$key == date("Ymd")){
                                $selected_count = $val;
                                $output->list[$_k]->selected = true;
                            }else{
                                $output->list[$_k]->selected = false;
                            }
                            $output->list[$_k]->val = $val;
                            $xml->item[] = sprintf('<item id="%s" name="%s" />',$_k,$_k);
                            $xml->value[0][] = $val;
                        }

                        $last_date = date("Ymd",strtotime($selected_date)-60*60*24*7);
                        $last_detail_status = $oCounterModel->getHourlyStatus('week', $last_date, $site_srl);
                        foreach($last_detail_status->list as $key => $val) {
                            $xml->value[1][] = $val;
                        }
            
             $xml->data = '<Graph><gdata title="Textyle Visitor" id="data"><fact>';
            $xml->data .= join("",$xml->item);
            $xml->data .= "</fact><subFact>";
            $xml->data .='<item id="0"><data name="'.$xml->selected_title.'">'. join("|",$xml->value[0]) .'</data></item>';
            $xml->data .='<item id="1"><data name="'.$xml->last_title.'">'. join("|",$xml->value[1]) .'</data></item>';
            $xml->data .= '</subFact></gdata></Graph>';
            
          //  var_dump($xml->value[0]);
            header('Content-Type: text/xml');
			echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>" . "\n";
			echo "<sunday>" . $xml->value[0][0] . "</sunday>";
			echo "<monday>" . $xml->value[0][1] . "</monday>";
			echo "<tuesday>" . $xml->value[0][2] . "</tuesday>";
			echo "<wednesday>" . $xml->value[0][3] . "</wednesday>";
			echo "<thursday>" . $xml->value[0][4] . "</thursday>";
			echo "<friday>" . $xml->value[0][5] . "</friday>";
			echo "<saturday>" . $xml->value[0][6] . "</saturday>";
			echo "</response>";
			exit;
        }
        
            function procmobile_communicationExtraMenuList()
            {
            if(!Context::get('is_logged')) $this->logout_message();
            $oTextyleModel = &getModel('textyle');
            $site_srl = Context::get("site_srl");
            $config = $oTextyleModel->getModulePartConfig($this->module_srl);
            Context::set('config',$config);

            $args->site_srl = $site_srl;
            $output = executeQueryArray('textyle.getExtraMenus',$args);
            //var_dump($output);
            
            header('Content-Type: text/xml');
			echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>" . "\n";
            
            foreach( $output->data as $page )
            {
            	echo "<textylePage>";
            	echo "<module_srl>" . $page->module_srl . "</module_srl>";
            	echo "<module>" . $page->module . "</module>";
            	echo "<mid>" . $page->mid . "</mid>";
            	echo "<name>" . $page->name . "</name>";
            	echo "<type>" . $page->type . "</type>";
            	echo "</textylePage>";
            }
            echo "</response>";
            exit;
            
            }
            
            function procmobile_communicationContentPage()
            {
            if(!Context::get('is_logged')) $this->logout_message();
            // set filter
            $menu_mid = Context::get('menu_mid');
            $site_srl = Context::get('site_srl');
            
            if($menu_mid)
            	{
                $oModuleModel = &getModel('module');
                $module_info = $oModuleModel->getModuleInfoByMid($menu_mid,$site_srl);
                if(!$module_info) return new Object(-1,'msg_invalid_request');
                
                $oWidgetController = &getController('widget');
                $buff = trim($module_info->content);
                $oXmlParser = new XmlParser();
                $xml_doc = $oXmlParser->parse(trim($buff));
                $document_srl = $xml_doc->img->attrs->document_srl;
                $args->module_srl = $module_info->module_srl;
                
                $oDocumentModel = &getModel('document');
                $oDocument = $oDocumentModel->getDocument($document_srl,false,false);
                
                $variables = $oDocument->variables;
                echo $variables['content'];
                exit;
            	}
            }
            
            function procmobile_communicationToolConfig()
            {
            if(!Context::get('is_logged')) $this->logout_message();
            $module_srl = Context::get('module_srl');

			$oTextyleModel = &getModel('textyle');
			$textyle = $oTextyleModel->getTextyle($module_srl);
			
			$editor = $textyle->getPostEditorSkin();
			//var_dump($var);	
			
			$fontFamily = $textyle->getFontFamily();
			//var_dump($var2);
			
			$fontSize = $textyle->getFontSize();
			//var_dump($var3);
			
			$usePrefix = $textyle->getPostUsePrefix();
			
			$prefix = $textyle->getPostPrefix(true);
			//var_dump($var4);
			
			$useSuffix = $textyle->getPostUseSuffix();
			
			$suffix = $textyle->getPostSuffix(true);
			//var_dump($var5);
			
			header('Content-Type: text/xml');
			echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>" . "\n";
			echo "<editor>" . $editor . "</editor>";
			echo "<fontFamily>" . $fontFamily . "</fontFamily>";
			echo "<fontSize>" . $fontSize . "</fontSize>";
			echo "<usePrefix>" . $usePrefix . "</usePrefix>";
			echo "<prefix>" . $prefix . "</prefix>";
			echo "<useSuffix>" . $useSuffix . "</useSuffix>";
			echo "<suffix>" . $suffix . "</suffix>";
			echo "</response>";
			exit;
			}
			
			function procmobile_communicationGetSkins()
			{
			if(!Context::get('is_logged')) $this->logout_message();
				$oModuleModel = &getModel('module');
			$module_path = _XE_PATH_ . 'modules/textyle/';
            $skins = $oModuleModel->getSkins($module_path);
            if(count($skins)) {
                foreach($skins as $skin_name => $info) {
                    $large_screenshot = $module_path.'skins/'.$skin_name.'/screenshots/large.jpg';
                    if(!file_exists($large_screenshot)) $large_screenshot = $module_path.'tpl/img/@large.jpg';
                    $small_screenshot = $module_path.'skins/'.$skin_name.'/screenshots/small.jpg';
                    if(!file_exists($small_screenshot)) $small_screenshot = $module_path.'tpl/img/@small.jpg';

                    unset($obj);
                    $obj->title = $info->title;
                    $obj->description = $info->description;
                    $_arr_author = array();
                    for($i=0,$c=count($info->author);$i<$c;$i++) {
                        $name =  $info->author[$i]->name;
                        $homepage = $info->author[$i]->homepage;
                        if($homepage) $_arr_author[] = '<a href="'.$homepage.'" onclick="window.open(this.href); return false;">'.$name.'</a>';
                        else $_arr_author[] = $name;
                    }
                    $obj->author = implode(',',$_arr_author);
                    $obj->large_screenshot = $large_screenshot;
                    $obj->small_screenshot = $small_screenshot;
                    $obj->date = $info->date;
                    $output[$skin_name] = $obj;
                }
            }
            
            header('Content-Type: text/xml');
			echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>" . "\n";
			
			foreach( $output as $key => $skin)
			{
				echo "<skin>";
				echo "<id>" . $key . "</id>";
				echo "<name>" . $skin->title . "</name>";
				echo "<description>" . $skin->description . "</description>";
				echo "<large_ss>" . $skin->large_screenshot . "</large_ss>";
				echo "<small_ss>" . $skin->small_screenshot . "</small_ss>";
				echo "</skin>";
			}
			echo "</response>";
			exit;
			}
			
		function procmobile_communicationViewerData()
		{
		if(!Context::get('is_logged')) $this->logout_message();
				$arr_dates = array();
		//$timestamp = time();
		for($i=0;$i<=6;$i++)
		{
			$arr_dates[$i] = date("Ymd",  strtotime("-".$i." days"));
		}
		
		// create the counter model object
		$oCounterModel = &getModel('counter');
		// get a total count and daily count
		$site_module_info = Context::get('site_module_info');
		//$type = 'day';
		//$detail_status = $oCounterModel->getHourlyStatus($type, $selected_date, $site_module_info->site_srl);
		$detail_status = $oCounterModel->getStatus($arr_dates, $site_module_info->site_srl);
		//var_dump($detail_status);
		header('Content-Type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
		echo "<response>" . "\n";
		
			foreach( $detail_status as $value )
			{	
				echo "<day>";
				echo "<date>" . $value->regdate . "</date>";
				echo "<unique_visitor>" . $value->unique_visitor ."</unique_visitor>";
				echo "<pageview>" . $value->pageview . "</pageview>";
				echo "</day>";
			}
		echo "</response>";
		exit;
		}
		
		function procmobile_communicationGetLayout()
		{
		if(!Context::get('is_logged')) $this->logout_message();
			$layoutModule = &getModel('layout');
			$data = $layoutModule->getLayoutList();
			
			header('Content-Type: text/xml');
			echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
			echo "<response>" . "\n";
			foreach($data as $value)
			{
				
				echo "<layout>";
				echo "<layout_srl>" . $value->layout_srl . "</layout_srl>";
				echo "<layout_name>" . $value->layout . "</layout_name>";
				echo "<title>" . $value->title . "</title>";
				echo "</layout>";
			}
			echo "</response>";
			exit;
		}
		
		function procmobile_communicationRegistreForPopUp()
		{
			$id = Context::get('id');

			$oModuleController = &getController('module');
			$oModuleModel = &getModel('module');
			$moduleConfig = $oModuleModel->getModuleConfig('mobile_communication');
			
			if( !in_array($id,$moduleConfig->iOS) && isset($id)) $moduleConfig->iOS[] = $id;
			
			$oModuleController->updateModuleConfig('mobile_communication',$moduleConfig);
			
			var_dump($moduleConfig);
		}
		
		function procmobile_communicationPopUpComment($comment)
		{
		$oModuleModel = &getModel('module');
		
			// Put your device token here (without spaces):
		//$deviceToken = 'fc45b7cd0cea86ea92303cc42b4c611efb70a288efb76cf639a4e6c97cab3db0';

		// Put your private key's passphrase here:
		$passphrase = 'prince';

		// Put your alert message here:
		$message = "You received a new comment:\n" . substr($comment,3, -4);

		////////////////////////////////////////////////////////////////////////////////
		$dir = dirname(__FILE__);
		
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $dir. '/ck.pem');
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

		// Open a connection to the APNS server
		$fp = stream_socket_client(
	'ssl://gateway.sandbox.push.apple.com:2195', $err,
	$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

		if (!$fp)
	exit("Failed to connect: $err $errstr" . PHP_EOL);

	echo 'Connected to APNS' . PHP_EOL;

		// Create the payload body
		$body['aps'] = array(
		'alert' => $message,
		'sound' => 'default'
		);

		// Encode the payload as JSON
		$payload = json_encode($body);
		
		$moduleConfig = $oModuleModel->getModuleConfig('mobile_communication');
		
		
		foreach($moduleConfig->iOS as $deviceToken)
		{
			var_dump($deviceToken);
			// Build the binary notification
			$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

			// Send it to the server
			$result = fwrite($fp, $msg, strlen($msg));
		}
		

		if (!$result)
		echo 'Message not delivered' . PHP_EOL;
		else
		echo 'Message successfully delivered' . PHP_EOL;

		// 	Close the connection to the server
		fclose($fp);
		}
		
		function procmobile_communicationRegistreForPopUpAndroid()
		{
			$id = Context::get('id');

			$oModuleController = &getController('module');
			$oModuleModel = &getModel('module');
			$moduleConfig = $oModuleModel->getModuleConfig('mobile_communication');
			
			if( !in_array($id,$moduleConfig->Android) && isset($id)) $moduleConfig->Android[] = $id;
			
			$oModuleController->updateModuleConfig('mobile_communication',$moduleConfig);
			
			var_dump($moduleConfig);
		}
		
		function procmobile_communicationUnregistreForPopUpAndroid()
		{
			$id = Context::get('id');
			$oModuleController = &getController('module');
			$oModuleModel = &getModel('module');
			$moduleConfig = $oModuleModel->getModuleConfig('mobile_communication');

			$key = array_search($id, $moduleConfig->Android);

			if (false !== $key) {
   				 unset($moduleConfig->Android[$key]);
								}

                        $oModuleController->updateModuleConfig('mobile_communication',$moduleConfig);

		}

		function procmobile_communicationPopUpCommentAndroid($comment)
		{
			// Replace with real BROWSER API key from Google APIs
			$apiKey = "AIzaSyDYmdzJ7XfYxCvEMjA0NUEdO4cD4G8D3W8";


			$oModuleModel = &getModel('module');
			$moduleConfig = $oModuleModel->getModuleConfig('mobile_communication');

			// Set POST variables
			$url = 'https://android.googleapis.com/gcm/send';

			$fields = array(
                'registration_ids'  => $moduleConfig->Android,
                'data'              => array( "message" => $comment ),
                );

			$headers = array( 
                    'Authorization: key=' . $apiKey,
                    'Content-Type: application/json'
                );


			// Open connection
			$ch = curl_init();

			// Set the url, number of POST vars, POST data
			curl_setopt( $ch, CURLOPT_URL, $url );

			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );

			// Execute post
			$result = curl_exec($ch);

			// Close connection
			curl_close($ch);

			echo $result;
		}
                
                function procmobile_communicationGetThemes(){
                    // Get return all themes to mobile using built-in mthod
                    if(!Context::get('is_logged')) $this->logout_message();
                    $oAdminModel = &getAdminModel("admin");                    
                    // Get selected layout
			$theme_file = _XE_PATH_.'files/theme/theme_info.php';
			if(is_readable($theme_file)){
				@include($theme_file);
				$selected_layout = $theme_info->layout;
			}
			else{
				$oModuleModel = &getModel('module');
				$default_mid = $oModuleModel->getDefaultMid();
				$selected_layout = $default_mid->layout_srl;
			}
                    
                    
                    
                    
                    $themes = $oAdminModel->getThemeList();
                    header('Content-Type: text/xml');
                    echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
                    echo "<response>" . "\n";
                    foreach($themes as $theme){
                        echo "<theme>";
                        echo "<name>".$theme->name."</name>\n";
                        echo "<thumbnail>".$theme->thumbnail."</thumbnail>\n";
                        echo "<version>".$theme->version."</version>\n";
                        echo "<date>".$theme->date."</date>\n";
                        echo "<description>".$theme->description."</description>\n";                        
                        foreach($theme->publisher as $publisher){
                            echo "<publisher>\n";
                            echo "<name>".$publisher->name."</name>\n";
                            echo "<email>".$publisher->email_address."</email>\n";                            
                            echo "</publisher>\n";
                        }                        
                        echo "<layout_srl>".$theme->layout_info->layout_srl."</layout_srl>\n";
                        echo "<selected_layout>".(($selected_layout==$theme->layout_info->layout_srl)?1:0)."</selected_layout>";
                        foreach($theme->skin_infos as $module_name => $skin_info){
                            echo "<skin>\n";
                            echo "<module>". $module_name. "-skin"."</module>\n";
                            echo "<name>". $skin_info->name."</name>\n";
                            echo "</skin>\n";
                        }                                          
                        echo "</theme>";
                    }
                    echo "</response>\n";
                    exit();
                }
                
                
}
?>