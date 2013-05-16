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
