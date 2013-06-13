<?php
	/**
	 * trackbackAdminView class
	 * trackback module admin view class
	 *
	 * @author Arnia (dev@karybu.org)
	 * @package /modules/trackback
	 * @version 0.1
	 */
    class trackbackAdminView extends trackback {
		/**
		 * Initialization
		 * @return void
		 */
        function init() {
        }
        function getGrid(){
            global $lang;
            $grid = new \Karybu\Grid\Backend();
            $grid->setId('trackbackListTable');
            $grid->setMassSelectName('cart');
            $grid->addColumn('title', 'link', array(
                'index' => 'title',
                'header'=> $lang->title,
                'link_key'=>'trackback_url',
                'target'=>'_blank',
                'length'=>'40'
            ));
            $grid->addColumn('blog_name', 'link', array(
                'index' => 'blog_name',
                'header'=> $lang->site,
                'link_key'=>'url',
                'target'=>'_blank',
                'length'=>'40'
            ));
            $grid->addColumn('regdate', 'date', array(
                'index' => 'regdate',
                'header'=> $lang->date,
                'format'=>'Y-m-d'
            ));
            $grid->addColumn('ipaddress', 'text', array(
                'index' => 'ipaddress',
                'header'=> $lang->ipaddress,
            ));
            return $grid;
        }
		/**
		 * Display output list (administrative)
		 * @return void
		 */
        function dispTrackbackAdminList() {
            // Wanted set
            $grid = $this->getGrid();
            $oModuleModel = &getModel('module');
            $config = $oModuleModel->getModuleConfig('trackback');
            Context::set('config',$config);

            // Options to get a list
            $args = new stdClass();
            $args->page = Context::get('page'); // / "Page
            $args->list_count = 10; // / "One page of posts to show the
            $args->page_count = 10; // / "Number of pages that appear in the page navigation
            $sortIndex = Context::get('sort_index');
            $grid->setSortIndex($sortIndex);
            //$args->sort_index = 'list_order'; // /< Sorting values
            $args->sort_index = $grid->getSortIndex();
            if (empty($args->sort_index)){
                $args->sort_index = 'list_order';
            }
            $sortOrder = Context::get('sort_order');
            $grid->setSortOrder($sortOrder);
            $args->sort_order = $grid->getSortOrder();
            //$args->sort_index = 'list_order'; // / "Sorting values
            $args->module_srl = Context::get('module_srl');
            // Get a list
            $oTrackbackAdminModel = &getAdminModel('trackback');
            $output = $oTrackbackAdminModel->getTotalTrackbackList($args);

            if (isset($output->data)){
                foreach ($output->data as $key=>$item){
                    $output->data[$key]->trackback_url = getUrl('','document_srl',$item->document_srl).'#trackback_'.$item->trackback_srl;
                    $output->data[$key]->mass_select_value = $item->trackback_srl;
                }
            }

            // To write to a template parameter settings
            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
            Context::set('trackback_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);
			//Security
			$security = new Security();
			$security->encodeHTML('config.');
			$security->encodeHTML('trackback_list..');
            $grid->setRows($output->data);
            Context::set('grid', $grid);
			// Set a template
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('trackback_list');
        }

    }
?>
