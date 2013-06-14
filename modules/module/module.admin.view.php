<?php
    /**
     * @class  moduleAdminView
     * @author Arnia (dev@karybu.org)
     * @brief admin view class of the module module
     **/

    class moduleAdminView extends module {
        protected $_favorites = null;
        /**
         * @brief Initialization
         **/
        function init() {
            // Set the template path
            $this->setTemplatePath($this->module_path.'tpl');
        }
        function getModuleGrid(){
            global $lang;
            $grid = new \Karybu\Grid\Backend();
            $grid->setAllowMassSelect(false)
                ->setShowOrderNumberColumn(false)
                ->addCssClass('easyList dsTg');
            $grid->addColumn('favorite', 'favorite', array(
                'index' => 'favorite',
                'header'=> $lang->favorite,
                'selected'=>$this->getFavorites()
            ));
            $grid->addColumn('title', 'module', array(
                'index' => 'title',
                'header'=> $lang->title,
                'sortable'=>false,
                'tooltip'=>true,
                'tooltip_key'=>'description',
                'link_key'=>'main_url',
                'hide_on_empty'=>true,
                'bold'=>true

            ));
            $grid->addColumn('version', 'link', array(
                'index' => 'version',
                'header'=> $lang->version,
                'sortable'=>false
            ));
            $grid->addColumn('authors', 'text', array(
                'index' => 'authors',
                'header'=> $lang->author,
                'sortable'=>false
            ));
            $grid->addColumn('path', 'text', array(
                'index' => 'path',
                'header'=> $lang->path,
                'sortable'=>false
            ));
            return $grid;
        }
        /**
         * @brief Module admin page
         **/
        function dispModuleAdminContent() {
            $this->dispModuleAdminList();
        }

        /**
         * @brief Display a lost of modules
         **/
        function dispModuleAdminList() {
			// Obtain a list of modules
            $oAdminModel = &getAdminModel('admin');
			$oModuleModel = &getModel('module');
			$oAutoinstallModel = &getModel('autoinstall');

			$module_list = $oModuleModel->getModuleList();
			if(is_array($module_list)){
				foreach($module_list as $key => $val) {
					$module_list[$key]->delete_url = $oAutoinstallModel->getRemoveUrlByPath($val->path);

					// get easyinstall need update
					$packageSrl = $oAutoinstallModel->getPackageSrlByPath($val->path);
					$package = $oAutoinstallModel->getInstalledPackages($packageSrl);
					$module_list[$key]->need_autoinstall_update = isset($package[$packageSrl]->need_update) ? $package[$packageSrl]->need_update : null;

					// get easyinstall update url
					if ($module_list[$key]->need_autoinstall_update == 'Y')
					{
						$module_list[$key]->update_url = $oAutoinstallModel->getUpdateUrlByPackageSrl($packageSrl);
					}
                    if (isset($val->author) && is_array($val->author)){
                        $authors = array();
                        foreach ($val->author as $author){
                            if (!empty($author->homepage) && !empty($author->name)){
                                $authors[] = '<a href="'.$author->homepage.'" target="_blank">'.$author->name.'</a>';
                            }
                            elseif(!empty($author->name)){
                                $authors[] = $author->name;
                            }
                        }
                        $module_list[$key]->authors = implode(" ", $authors);
                    }
                    if (!empty($val->admin_index_act)){
                        $module_list[$key]->main_url = getUrl('','module','admin','act',$val->admin_index_act);
                    }
				}
			}

            Context::set('favoriteModuleList', $this->getFavorites());
			Context::set('module_list', $module_list);

			$security = new Security();
			//$security->encodeHTML('module_list....');

            $grid = $this->getModuleGrid();
            $grid->setRows($module_list);
            Context::set('grid', $grid);
            // Set a template file
            $this->setTemplateFile('module_list');

        }
        function getFavorites(){
            if (is_null($this->_favorites)){
                $oAdminModel = &getAdminModel('admin');
                $output = $oAdminModel->getFavoriteList('0');

                $favoriteList = $output->get('favoriteList');
                $favoriteModuleList = array();
                if ($favoriteList){
                    foreach($favoriteList as $favorite => $favorite_info){
                        $favoriteModuleList[] = $favorite_info->module;
                    }
                }
                $this->_favorites = $favoriteModuleList;
            }
            return $this->_favorites;
        }
        function getGrid(){
            global $lang;
            $grid = new \Karybu\Grid\Backend();
            $grid->setAllowMassSelect(false);
            $grid->addColumn('title', 'text', array(
                'index' => 'title',
                'header'=> $lang->category_title,
            ));
            $grid->addColumn('regdate', 'date', array(
                'index' => 'regdate',
                'header'=> $lang->regdate,
                'format'=>'Y-m-d'
            ));
            $grid->addColumn('actions', 'action', array(
                'index'         => 'actions',
                'header'        => $lang->actions,
                'wrapper_top'   => '<div class="kActionIcons">',
                'wrapper_bottom'=> '</div>'
            ));
            //view action
            $actionConfig = array(
                'title'=>$lang->cmd_modify,
                'url_params'=>array('module_category_srl'=>'module_category_srl'),
                'module'=>'admin',
                'act'=>'dispModuleAdminCategory',
                'icon_class' => 'kEdit'
            );

            $action = new \Karybu\Grid\Action\Action($actionConfig);
            $grid->getColumn('actions')->addAction('edit',$action);
            $actionConfig = array(
                'title'=>$lang->cmd_delete,
                'confirm'=>$lang->confirm_delete,
                'params'=>array('module_category_srl'=>'module_category_srl'),
                'icon_class' => 'kDelete',
                'js_action' => 'doUpdateCategory'
            );
            $action = new \Karybu\Grid\Action\Confirm($actionConfig);
            $grid->getColumn('actions')->addAction('delete',$action);
            return $grid;
        }
        /**
         * @brief Module Categories
         **/
        function dispModuleAdminCategory() {
            $module_category_srl = Context::get('module_category_srl');

            // Obtain a list of modules
            $oModuleModel = &getModel('module');
            // Display the category page if a category is selected
			//Security
			$security = new Security();				
            if($module_category_srl) {
                $selected_category  = $oModuleModel->getModuleCategory($module_category_srl);
                Context::set('selected_category', $selected_category);

				//Security
				$security->encodeHTML('selected_category.title');				

				// Set a template file
                $this->setTemplateFile('category_update_form');
            // If not selected, display a list of categories
            } else {
                $category_list = $oModuleModel->getModuleCategories();
                Context::set('category_list', $category_list);
                $grid = $this->getGrid();
                Context::set('grid', $grid);
                $sortIndex = Context::get('sort_index');
                $grid->setSortIndex($sortIndex);
                //$args->sort_index = 'list_order'; // /< Sorting values
                $args = new stdClass();
                Context::set('sort_index',$grid->getSortIndex());
                $sortOrder = Context::get('sort_order');
                $grid->setSortOrder($sortOrder);
                Context::set('sort_order',$grid->getSortOrder());
                $grid->setRows($category_list);

				//Security
				$security->encodeHTML('category_list..title');

				// Set a template file
                $this->setTemplateFile('category_list');
            }
        }

        /**
         * @brief Feature to copy module
         **/
        function dispModuleAdminCopyModule() {
            // Get a target module to copy
            $module_srl = Context::get('module_srl');
            // Get information of the module
            $oModuleModel = &getModel('module');
			$columnList = array('module_srl', 'module', 'mid', 'browser_title');
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl, $columnList);
            Context::set('module_info', $module_info);
            // Set the layout to be pop-up
			$this->setLayoutPath('./common/tpl');
            $this->setLayoutFile('popup_layout');
            // Set a template file
            $this->setTemplateFile('copy_module');
        }

        /**
         * @brief Applying the default settings to all modules
         **/
        function dispModuleAdminModuleSetup() {
            $module_srls = Context::get('module_srls');

            $modules = explode(',',$module_srls);
            if(!count($modules)) if(!$module_srls) return new Object(-1,'msg_invalid_request');

            $oModuleModel = &getModel('module');
			$columnList = array('module_srl', 'module');
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($modules[0], $columnList);
            // Get a skin list of the module
            $skin_list = $oModuleModel->getSkins('./modules/'.$module_info->module);
            Context::set('skin_list',$skin_list);
            // Get a layout list
            $oLayoutMode = &getModel('layout');
            $layout_list = $oLayoutMode->getLayoutList();
            Context::set('layout_list', $layout_list);
            // Get a list of module categories
            $module_category = $oModuleModel->getModuleCategories();
            Context::set('module_category', $module_category);

			$security = new Security();				
			$security->encodeHTML('layout_list..title','layout_list..layout');
			$security->encodeHTML('skin_list....');
			$security->encodeHTML('module_category...');

			// Set the layout to be pop-up
			$this->setLayoutPath('./common/tpl');
            $this->setLayoutFile('popup_layout');
            // Set a template file
            $this->setTemplateFile('module_setup');
        }

        /**
         * @brief Applying module permission settings to all modules
         **/
        function dispModuleAdminModuleGrantSetup() {
            $module_srls = Context::get('module_srls');

            $modules = explode(',',$module_srls);
            if(!count($modules)) if(!$module_srls) return new Object(-1,'msg_invalid_request');

            $oModuleModel = &getModel('module');
			$columnList = array('module_srl', 'module', 'site_srl');
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($modules[0], $columnList);
            $xml_info = $oModuleModel->getModuleActionXml($module_info->module);
            $source_grant_list = $xml_info->grant;
            // Grant virtual permissions for access and manager
            $grant_list->access->title = Context::getLang('grant_access');
            $grant_list->access->default = 'guest';
            if(count($source_grant_list)) {
                foreach($source_grant_list as $key => $val) {
                    if(!$val->default) $val->default = 'guest';
                    if($val->default == 'root') $val->default = 'manager';
                    $grant_list->{$key} = $val;
                }
            }
            $grant_list->manager->title = Context::getLang('grant_manager');
            $grant_list->manager->default = 'manager';
            Context::set('grant_list', $grant_list);
            // Get a list of groups
            $oMemberModel = &getModel('member');
            $group_list = $oMemberModel->getGroups($module_info->site_srl);
            Context::set('group_list', $group_list);
			$security = new Security();				
			$security->encodeHTML('group_list..title');

			// Set the layout to be pop-up
			$this->setLayoutPath('./common/tpl');
            $this->setLayoutFile('popup_layout');
            // Set a template file
            $this->setTemplateFile('module_grant_setup');
        }


        /**
         * @brief Language codes
         **/
        function dispModuleAdminLangcode() {
            // Get the language file of the current site
            $site_module_info = Context::get('site_module_info');
            $args = new stdClass();
            $args->site_srl = (int)$site_module_info->site_srl;
			$args->langCode = Context::get('lang_type');
            $args->page = Context::get('page'); // /< Page
            $args->list_count = 30; // /< the number of posts to display on a single page
            $args->page_count = 5; // /< the number of pages that appear in the page navigation
            $args->sort_index = 'name';
            $args->order_type = 'asc';
            $args->search_target = Context::get('search_target'); // /< search (title, contents ...)
            $args->search_keyword = Context::get('search_keyword'); // /< keyword to search

			$oModuleAdminModel = &getAdminModel('module');
			$output = $oModuleAdminModel->getLangListByLangcode($args);

            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
            Context::set('lang_code_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);
			
			if(Context::get('module') != 'admin')
			{
				$this->setLayoutPath('./common/tpl');
				$this->setLayoutFile('popup_layout');
			}
            // Set a template file
            $this->setTemplateFile('module_langcode');
        }

		function dispModuleAdminFileBox(){
            $oModuleModel = &getModel('module');
            $output = $oModuleModel->getModuleFileBoxList();
			$page = Context::get('page');
			$page = $page?$page:1;
            Context::set('filebox_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);
            Context::set('page', $page);
            $this->setTemplateFile('adminFileBox');
		}
    }
?>
