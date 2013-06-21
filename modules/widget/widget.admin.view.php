<?php
    /**
     * @class  widgetAdminView
     * @author Arnia (dev@karybu.org)
     * @brief admin view class for widget modules
     **/

    class widgetAdminView extends widget {

        /**
         * @brief Initialization
         **/
        function init() {
            $this->setTemplatePath($this->module_path.'tpl');
        }
        public function getGrid(){
            global $lang;
            $grid = new \Karybu\Grid\Backend();
            $grid->setAllowMassSelect(false)
                ->addCssClass('easyList')
                ->addCssClass('dsTg');
            $grid->addColumn('title', 'text', array(
                'index' => 'title',
                'header'=> $lang->widget_name,
                'sortable'=>false,
                'tooltip'=>true,
                'tooltip_key'=>'description'
            ));
            $grid->addColumn('version', 'text', array(
                'index' => 'version',
                'header'=> $lang->version,
                'sortable'=>false
            ));
            $grid->addColumn('author', 'author', array(
                'index' => 'author',
                'header'=> $lang->author,
                'sortable'=>false,
                'author'=>'author'
            ));
            $grid->addColumn('path', 'text', array(
                'index' => 'path',
                'header'=> $lang->path,
                'sortable'=>false
            ));
            $grid->addColumn('actions', 'action', array(
                'index'         => 'actions',
                'header'        => $lang->actions,
                'wrapper_top'   => '<div class="kActionIcons">',
                'wrapper_bottom'=> '</div>'
            ));
            //configure action
            $actionConfig = array(
                'title'=>$lang->cmd_generate_code,
                'url_params'=>array('selected_widget'=>'widget'),
                'module'=>'admin',
                'act'=>'dispWidgetAdminGenerateCode',
                'icon_class' => 'kGenerateCode'
            );
            $action = new \Karybu\Grid\Action\Action($actionConfig);
            $grid->getColumn('actions')->addAction('generate_code',$action);
            return $grid;
        }
        /**
         * @brief Showing a list of widgets
         **/
        function dispWidgetAdminDownloadedList() {
            // Set widget list
            $oWidgetModel = &getModel('widget');
            $widget_list = $oWidgetModel->getDownloadedWidgetList();

			$security = new Security($widget_list);
			$widget_list = $security->encodeHTML('..', '..author..');

			foreach($widget_list as $no => $widget)
			{
				$widget_list[$no]->description = nl2br(trim($widget->description));
			}

            Context::set('widget_list', $widget_list);
			Context::set('tCount', count($widget_list));
            $grid = $this->getGrid();
            Context::set('grid', $grid);
            $grid->setRows($widget_list);
            $grid->setTotalCount(count($widget_list));
            $this->setTemplateFile('downloaded_widget_list');
        }

		function dispWidgetAdminGenerateCode()
		{
			$oView = &getView('widget');
			Context::set('in_admin', true);
			$this->setTemplateFile('widget_generate_code');
			return $oView->dispWidgetGenerateCode();
		}



    }
?>
