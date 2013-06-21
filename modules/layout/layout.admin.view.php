<?php
    /**
     * @class  layoutAdminView
     * @author Arnia (dev@karybu.org)
     * admin view class of the layout module
     **/

    class layoutAdminView extends layout {

        /**
         * Initialization
		 * @return void
         **/
        function init() {
            $this->setTemplatePath($this->module_path.'tpl');
        }
        function getGrid(){
            global $lang;
            $grid = new \Karybu\Grid\Backend();
            $grid->setAllowMassSelect(false);
            $grid->addColumn('title', 'link', array(
                'index' => 'title',
                'header'=> $lang->layout_name,
                'link_key'=>'url',
                'sortable'=>false,
            ));
            $grid->addColumn('version', 'text', array(
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
		 * Display a installed layout list
		 * @return void
		 **/
		function dispLayoutAdminInstalledList() {
			$type = Context::get('type');
			$type = ($type != 'M') ? 'P' : 'M';

			// Set a layout list
			$oLayoutModel = &getModel('layout');
			$layout_list = $oLayoutModel->getDownloadedLayoutList($type, true);
			if(!is_array($layout_list))
			{
				$layout_list = array();
			}
			
			if($type == 'P')
			{
				// get Theme layout
				$oAdminModel = &getAdminModel('admin');
				$themeList = $oAdminModel->getThemeList();
				$themeLayoutList = array();
				foreach($themeList as $themeInfo){
					if(strpos($themeInfo->layout_info->name, '.') === false) continue;
					$themeLayoutList[] = $oLayoutModel->getLayoutInfo($themeInfo->layout_info->name, null, 'P');
				}
				$layout_list = array_merge($layout_list, $themeLayoutList);
				//$layout_list[] = $oLayoutModel->getLayoutInfo('faceoff', null, 'P');
			}

			$pcLayoutCount = $oLayoutModel->getInstalledLayoutCount('P');
			$mobileLayoutCount = $oLayoutModel->getInstalledLayoutCount('M');
			Context::set('pcLayoutCount', $pcLayoutCount);
			Context::set('mobileLayoutCount', $mobileLayoutCount);
			$this->setTemplateFile('installed_layout_list');

			$security = new Security($layout_list);
			$layout_list = $security->encodeHTML('..', '..author..');
			//Security
			$security = new Security();
			$security->encodeHTML('layout_list..layout','layout_list..title');						
			
			foreach($layout_list as $no => $layout_info)
			{
                if (empty($layout_info->title)){
                    $layout_list[$no]->title = isset($layout_info->title) ? $layout_info->title : '';
                    $layout_list[$no]->version = '-';
                    $layout_list[$no]->author = '-';
                }
                $layout_list[$no]->url = getUrl('act', 'dispLayoutAdminInstanceList', 'type', (isset($layout_info->type) ? $layout_info->type : ''), 'layout', (isset($layout_info->layout) ? $layout_info->layout : ''));
                $layout_list[$no]->description = isset($layout_info->description) ? nl2br(trim($layout_info->description)) : '';
                if (is_array($layout_info->author)){
                    $authors = array();
                    foreach ($layout_info->author as $author){
                        if (!empty($author->homepage) && !empty($author->name)){
                            $authors[] = '<a href="'.$author->homepage.'"target="_blank">'.$author->name.'</a>';
                        }
                        elseif (!empty($author->name)){
                            $authors[] = $author->name;
                        }
                    }
                    $layout_list[$no]->authors = implode(' ',$authors);
                }
			}
            $grid = $this->getGrid();
            $grid->setTotalCount(count($layout_list));
            $grid->setRows($layout_list);
            Context::set('grid', $grid);
			Context::set('layout_list', $layout_list);
		}
        function getLayoutInstanceGrid(){
            global $lang;
            $grid = new \Karybu\Grid\Backend();
            $grid->setAllowMassSelect(false)
                    ->setShowOrderNumberColumn(true);
            $grid->addColumn('layout', 'text', array(
                'index' => 'layout',
                'header'=> $lang->layout_name,
            ));
            $grid->addColumn('title', 'text', array(
                'index' => 'title',
                'header'=> $lang->title,
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
            //configure action
            $actionConfig = array(
                'title'=>$lang->cmd_layout_management,
                'url_params'=>array('layout_srl'=>'layout_srl'),
                'module'=>'admin',
                'act'=>'dispLayoutAdminModify',
                'icon_class' => 'kConfigure'
            );
            $action = new \Karybu\Grid\Action\Action($actionConfig);
            $grid->getColumn('actions')->addAction('configure',$action);
            //edit action
            $actionConfig = array(
                'title'=>$lang->cmd_layout_edit,
                'url_params'=>array('layout_srl'=>'layout_srl'),
                'module'=>'admin',
                'act'=>'dispLayoutAdminEdit',
                'icon_class' => 'kEdit'
            );
            $action = new \Karybu\Grid\Action\Action($actionConfig);
            $grid->getColumn('actions')->addAction('edit',$action);
            //copy action
            $actionConfig = array(
                'title'=>$lang->cmd_layout_copy,
                'url_params'=>array('layout_srl'=>'layout_srl'),
                'module'=>'layout',
                'act'=>'dispLayoutAdminCopyLayout',
                'icon_class' => 'kCopy',
                'popup'=>true
            );
            $action = new \Karybu\Grid\Action\Action($actionConfig);
            $grid->getColumn('actions')->addAction('copy',$action);

            //edit action
            $actionConfig = array(
                'title'=>$lang->cmd_delete,
                'params'=>array('layout_srl'=>'layout_srl'),
                'module'=>'layout',
                'act'=>'procLayoutAdminDelete',
                'icon_class' => 'kDelete',
                'ruleset'=>'deleteLayout',
                'class_name'=>'layout_delete_form'
            );
            $action = new \Karybu\Grid\Action\Delete($actionConfig);
            $grid->getColumn('actions')->addAction('delete',$action);

            return $grid;
        }
		/**
		 * Display list of pc layout all instance
		 * @return void|Object (void : success, Object : fail)
		 */
		function dispLayoutAdminAllInstanceList()
		{
			$type = Context::get('type');

			if (!in_array($type, array('P', 'M'))) $type = 'P';

			$oLayoutModel = &getModel('layout');
			
			$pcLayoutCount = $oLayoutModel->getInstalledLayoutCount('P');
			$mobileLayoutCount = $oLayoutModel->getInstalledLayoutCount('M');
			Context::set('pcLayoutCount', $pcLayoutCount);
			Context::set('mobileLayoutCount', $mobileLayoutCount);


            $grid = $this->getLayoutInstanceGrid();
            $sortIndex = Context::get('sort_index');
            $grid->setSortIndex($sortIndex);
            //$args->sort_index = 'list_order'; // /< Sorting values
            $args = new stdClass();
            Context::set('sort_index',$grid->getSortIndex());
            $sortOrder = Context::get('sort_order');
            $grid->setSortOrder($sortOrder);
            Context::set('sort_order',$grid->getSortOrder());

            $columnList = array('layout_srl', 'layout', 'module_srl', 'title', 'regdate');
            $_layout_list = $oLayoutModel->getLayoutInstanceList(0, $type, null, $columnList);

			$layout_list = array();
			foreach($_layout_list as $key=>$item)
			{//echo "<pre>"; print_r($item);echo "</pre>";
                $layout_info = $oLayoutModel->getLayoutInfo($item->layout, null, $type);
                //$_layout_list[$key]->title = $layout_info->title;
				if(empty($layout_list[$item->layout]))
				{
					$layout_list[$item->layout] = array();
					$layout_info = $oLayoutModel->getLayoutInfo($item->layout, null, $type);
					if ($layout_info)
					{
						$layout_list[$item->layout]['title'] = $layout_info->title;
					}
				}
//
				$layout_list[$item->layout][] = $item;
			}

			//usort($layout_list, array($this, 'sortLayoutInstance'));

			Context::set('layout_list', $layout_list);

			$this->setTemplateFile('layout_all_instance_list');
            $grid->setRows($_layout_list);
            Context::set('grid', $grid);
            $grid->setTotalCount(count($_layout_list));
			$security = new Security();
			$security->encodeHTML('layout_list..');
		}

		/**
		 * Sort layout instance by layout name, instance name
		 */
		function sortLayoutInstance($a, $b)
		{
			$aTitle = strtolower($a['title']);
			$bTitle = strtolower($b['title']);

			if($aTitle == $bTitle)
			{
				return 0;
			}

			return ($aTitle < $bTitle) ? -1 : 1;
		}

		/**
		 * Display list of pc layout instance
		 * @return void|Object (void : success, Object : fail)
		 */
		function dispLayoutAdminInstanceList()
		{
			$type = Context::get('type');
			$layout = Context::get('layout');

			if (!in_array($type, array('P', 'M'))) $type = 'P';
			if (!$layout) return $this->stop('msg_invalid_request');

			$oLayoutModel = &getModel('layout');
			$layout_info = $oLayoutModel->getLayoutInfo($layout, null, $type);
			if (!$layout_info) return $this->stop('msg_invalid_request');

			Context::set('layout_info', $layout_info);

			$columnList = array('layout_srl', 'layout', 'module_srl', 'title', 'regdate');
			$layout_list = $oLayoutModel->getLayoutInstanceList(0, $type, $layout, $columnList);
			Context::set('layout_list', $layout_list);

			$this->setTemplateFile('layout_instance_list');

			$security = new Security();
			$security->encodeHTML('layout_list..');
		}

		/**
         * Insert Layout details
		 * @return void
         **/
        function dispLayoutAdminModify() {
            // Set the layout after getting layout information
            $layout_srl = Context::get('layout_srl');

			// Get layout information
            $oLayoutModel = &getModel('layout');
            $layout_info = $oLayoutModel->getLayout($layout_srl);

			// Error appears if there is no layout information is registered
            if(!$layout_info) return $this->stop('msg_invalid_request');

            // If faceoff, no need to display the path
            if($layout_info->type == 'faceoff') unset($layout_info->path);

            // Get a menu list
            $oMenuAdminModel = &getAdminModel('menu');
            $menu_list = $oMenuAdminModel->getMenus();
            Context::set('menu_list', $menu_list);

			$security = new Security();
			$security->encodeHTML('menu_list..');

			$security = new Security($layout_info);
			$layout_info = $security->encodeHTML('.', 'author..', 'extra_var..');

			$layout_info->description = nl2br(trim($layout_info->description));
			if (!is_object($layout_info->extra_var)) $layout_info->extra_var = new StdClass();
			foreach($layout_info->extra_var as $var_name => $val)
			{
				if (isset($layout_info->{$var_name}->description))
					$layout_info->{$var_name}->description = nl2br(trim($val->description));
			}
			Context::set('selected_layout', $layout_info);

			$this->setTemplateFile('layout_modify');
        }

        /**
         * Edit layout codes
		 * @return void
         **/
        function dispLayoutAdminEdit() {
            // Set the layout with its information
            $layout_srl = Context::get('layout_srl');
            // Get layout information
            $oLayoutModel = &getModel('layout');
            $layout_info = $oLayoutModel->getLayout($layout_srl);
            // Error appears if there is no layout information is registered
            if(!$layout_info) return $this->dispLayoutAdminInstalledList();

            // Get Layout Code
            $oLayoutModel = &getModel('layout');
            $layout_file = $oLayoutModel->getUserLayoutHtml($layout_info->layout_srl);
            if(!file_exists($layout_file)){
                // If faceoff
                if($oLayoutModel->useDefaultLayout($layout_info->layout_srl)){
                    $layout_file  = $oLayoutModel->getDefaultLayoutHtml($layout_info->layout);
                }else{
                    $layout_file = sprintf('%s%s', $layout_info->path, 'layout.html');
                }
            }

            $layout_css_file = $oLayoutModel->getUserLayoutCss($layout_info->layout_srl);
            if(!file_exists($layout_css_file)){
                // If faceoff
                if($oLayoutModel->useDefaultLayout($layout_info->layout_srl)){
                    $layout_css_file  = $oLayoutModel->getDefaultLayoutCss($layout_info->layout);
                }else{
                    $layout_css_file = sprintf('%s%s', $layout_info->path, 'css/style.css');
                }
            }
            if(file_exists($layout_css_file)){
                $layout_code_css = FileHandler::readFile($layout_css_file);
                Context::set('layout_code_css', $layout_code_css);
            }

            $layout_code = FileHandler::readFile($layout_file);
            Context::set('layout_code', $layout_code);

            // set User Images
            $layout_image_list = $oLayoutModel->getUserLayoutImageList($layout_info->layout_srl);
            Context::set('layout_image_list', $layout_image_list);

            $layout_image_path = $oLayoutModel->getUserLayoutImagePath($layout_info->layout_srl);
            Context::set('layout_image_path', $layout_image_path);
            // Set widget list
            $oWidgetModel = &getModel('widget');
            $widget_list = $oWidgetModel->getDownloadedWidgetList();
            Context::set('widget_list', $widget_list);

            $this->setTemplateFile('layout_edit');

			$security = new Security($layout_info);
			$layout_info = $security->encodeHTML('.', '.author..');
			Context::set('selected_layout', $layout_info);
			
			//Security
			$security = new Security();
			$security->encodeHTML('layout_list..');	
			$security->encodeHTML('layout_list..author..');	
			
			$security = new Security();
			$security->encodeHTML('layout_code_css', 'layout_code', 'widget_list..title');
        }

        /**
         * Preview a layout
		 * @return void|Object (void : success, Object : fail)
         **/
        function dispLayoutAdminPreview() {
            $layout_srl = Context::get('layout_srl');
            $code = Context::get('code');
            $code_css = Context::get('code_css');
            if(!$layout_srl || !$code) return new Object(-1, 'msg_invalid_request');
            // Get the layout information
            $oLayoutModel = &getModel('layout');
            $layout_info = $oLayoutModel->getLayout($layout_srl);
            if(!$layout_info) return new Object(-1, 'msg_invalid_request');
            // Separately handle the layout if its type is faceoff
            //if($layout_info && $layout_info->type == 'faceoff') $oLayoutModel->doActivateFaceOff($layout_info);
            // Apply CSS directly
            Context::addHtmlHeader("<style type=\"text/css\" charset=\"UTF-8\">".$code_css."</style>");
            // Set names and values of extra_vars to $layout_info
            if($layout_info->extra_var_count) {
                foreach($layout_info->extra_var as $var_id => $val) {
                    $layout_info->{$var_id} = $val->value;
                }
            }
            // menu in layout information becomes an argument for Context:: set
            if($layout_info->menu_count) {
                foreach($layout_info->menu as $menu_id => $menu) {
                    if(file_exists($menu->php_file)) include($menu->php_file);
                    Context::set($menu_id, $menu);
                }
            }

            Context::set('layout_info', $layout_info);
            Context::set('content', Context::getLang('layout_preview_content'));
            // Temporary save the codes
            $edited_layout_file = sprintf('./files/cache/layout/tmp.tpl');
            FileHandler::writeFile($edited_layout_file, $code);

            // Compile
            $oTemplate = &TemplateHandler::getInstance();

            $layout_path = $layout_info->path;
            $layout_file = 'layout';

            $layout_tpl = $oTemplate->compile($layout_path, $layout_file, $edited_layout_file);
            Context::set('layout','none');
            // Convert widgets and others
            $oContext = &Context::getInstance();
            Context::set('layout_tpl', $layout_tpl);
            // Delete Temporary Files
            FileHandler::removeFile($edited_layout_file);
            $this->setTemplateFile('layout_preview');

        }

        /**
         * Modify admin layout of faceoff
		 * @deprecated
		 * @return void
         **/
        /*faceOff is deprecated for the moment*/
/*        function dispLayoutAdminLayoutModify(){
            // Get layout_srl
            $current_module_info = Context::get('current_module_info');
            $layout_srl = $current_module_info->layout_srl;
            // Remove the remaining tmp files because of temporarily saving
            // This part needs to be modified
            $delete_tmp = Context::get('delete_tmp');
            if($delete_tmp =='Y'){
                $oLayoutAdminController = &getAdminController('layout');
                $oLayoutAdminController->deleteUserLayoutTempFile($layout_srl);
            }

            $oLayoutModel = &getModel('layout');
            // layout file is used as a temp.
            $oLayoutModel->setUseUserLayoutTemp();
            // Apply CSS in inline style
            $faceoffcss = $oLayoutModel->_getUserLayoutFaceOffCss($current_module_info->layout_srl);

            $css = FileHandler::readFile($faceoffcss);
            $match = null;
            preg_match_all('/([^\{]+)\{([^\}]*)\}/is',$css,$match);
            for($i=0,$c=count($match[1]);$i<$c;$i++) {
                $name = trim($match[1][$i]);
                $css = trim($match[2][$i]);
                if(!$css) continue;
                $css = str_replace('./images/',Context::getRequestUri().$oLayoutModel->getUserLayoutImagePath($layout_srl),$css);
                $style[] .= sprintf('"%s":"%s"',$name,$css);
            }

            if(count($style)) {
                $script = '<script type="text/javascript"> var faceOffStyle = {'.implode(',',$style).'}; </script>';
                Context::addHtmlHeader($script);
            }

            $oTemplate = &TemplateHandler::getInstance();
            Context::set('content', $oTemplate->compile($this->module_path.'tpl','about_faceoff'));
            // Change widget codes in Javascript mode
            $oWidgetController = &getController('widget');
            $oWidgetController->setWidgetCodeInJavascriptMode();
            // Set a template file
            $this->setTemplateFile('faceoff_layout_edit');
        }*/

        /**
         * display used images info for faceoff
		 * @deprecated
		 * @return void
         **/
        /* faceoff is deprecated for the moment */
        /*
        function dispLayoutAdminLayoutImageList(){
            $layout_srl = Context::get('layout_srl');
            $oLayoutModel = &getModel('layout');
            // Image List
            $layout_image_list = $oLayoutModel->getUserLayoutImageList($layout_srl);
            Context::set('layout_image_list',$layout_image_list);
            // Path
            $layout_image_path = $oLayoutModel->getUserLayoutImagePath($layout_srl);
            Context::set('layout_image_path',$layout_image_path);

            $this->setLayoutFile('popup_layout');

            $this->setTemplateFile('layout_image_list');
        }
        */

        /**
         * Copy layout instance
		 * @return void
         */
        function dispLayoutAdminCopyLayout(){
			$layoutSrl = Context::get('layout_srl');

			$oLayoutModel = &getModel('layout');
			$layout = $oLayoutModel->getLayout($layoutSrl);

            Context::set('layout', $layout);
            $this->setTemplateFile('copy_layout');
        }
    }
?>
