<?php
	/**
	 * documentAdminView class
	 * Document admin view of the module class
	 *
	 * @author Arnia (dev@karybu.org)
	 * @package /modules/document
	 * @version 0.1
	 */
    class documentAdminView extends document {
		/**
		 * Initialization
		 * @return void
		 */
        function init() {
			// check current location in admin menu
			$oModuleModel = &getModel('module');
			$info = $oModuleModel->getModuleActionXml('document');
			foreach($info->menu AS $key => $menu)
			{
				if(in_array($this->act, $menu->acts))
				{
					Context::set('currentMenu', $key);
					break;
				}
			}
        }

		/**
		 * Display a list(administrative)
		 * @return void
		 */
        function dispDocumentAdminList() {
            // option to get a list
            $grid = $this->getGrid();
            $grid->removeColumn('declared');
            $sortIndex = Context::get('sort_index');
            $sortOrder = Context::get('sort_order');
            $grid->setSortIndex($sortIndex);
            $grid->setSortOrder($sortOrder);
            $args = new stdClass();
            $args->page = Context::get('page'); // /< Page
            $args->list_count = 30; // /< the number of posts to display on a single page
            $args->page_count = 10; // /< the number of pages that appear in the page navigation

            $args->search_target = Context::get('search_target'); // /< search (title, contents ...)
            $args->search_keyword = Context::get('search_keyword'); // /< keyword to search
            if ($grid->getSortIndex()){
                $args->sort_index = $grid->getSortIndex();
            }
            else {
                $args->sort_index = 'list_order'; // /< sorting value
            }
            $args->order_type = $grid->getSortOrder();

            $args->module_srl = Context::get('module_srl');

            // get a list
            $oDocumentModel = &getModel('document');
			$columnList = array('document_srl', 'title', 'member_srl', 'nick_name', 'readed_count', 'voted_count', 'blamed_count', 'regdate', 'ipaddress', 'status');
            $output = $oDocumentModel->getDocumentList($args, false, true, $columnList);
            $documents = array();
            foreach ($output->data as $key=>$document){
                $object = new stdClass();
                foreach ($document->variables as $k=>$v){
                    $object->$k = $v;
                }
                $object->mass_select_value = $document->document_srl;
                $object->url = getUrl('','document_srl',$document->document_srl);
                $documents[$key] = $object;
            }

			// get Status name list
			$statusNameList = $oDocumentModel->getStatusNameList();
            // Set values of document_model::getDocumentList() objects for a template
            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
            Context::set('document_list', $output->data);
            Context::set('status_name_list', $statusNameList);
            Context::set('page_navigation', $output->page_navigation);

			$oSecurity = new Security();
			$oSecurity->encodeHTML('document_list..variables.');

            // set a search option used in the template
            $count_search_option = count($this->search_option);
            for($i=0;$i<$count_search_option;$i++) {
                $search_option[$this->search_option[$i]] = Context::getLang($this->search_option[$i]);
            }
            Context::set('search_option', $search_option);
            $grid->setRows($documents);
            Context::set('grid', $grid);
            // Specify a template
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('document_list');
        }

        function getGrid(){
            global $lang;
            $grid = new \Karybu\Grid\Backend();
            $grid->setId('documentListTable');
            $grid->setMassSelectName('cart');
            $grid->addColumn('title', 'link', array(
                'index' => 'title',
                'header'=> $lang->title,
                'link_key'=>'url',
                'target'=>'_blank'
            ));
            $grid->addColumn('nick_name', 'member', array(
                'index' => 'nick_name',
                'header'=> $lang->nick_name,
                'masked'=>true,
                'title'=>'Info',
                'member_key'=>'member_srl'
            ));
            $grid->addColumn('declared', 'number', array(
                'index' => 'declared_count',
                'header'=> $lang->cmd_declare,
                'sort_index'=>'document_declared.declared_count'
            ));
            $grid->addColumn('readed_count', 'number', array(
                'index' => 'readed_count',
                'header'=> $lang->readed_count,
            ));
            $grid->addColumn('voted_count', 'number', array(
                'index' => 'voted_count',
                'header'=> $lang->cmd_vote. '(+)',
            ));
            $grid->addColumn('blamed_count', 'number', array(
                'index' => 'blamed_count',
                'header'=> '(-)',
            ));
            $grid->addColumn('regdate', 'date', array(
                'index' => 'regdate',
                'header'=> $lang->date,
                'format'=>'Y-m-d H:i'
            ));
            $grid->addColumn('ipaddress', 'text', array(
                'index' => 'ipaddress',
                'header'=> $lang->ipaddress,
            ));
            $oDocumentModel = &getModel('document');
            $grid->addColumn('status', 'options', array(
                'index' => 'status',
                'header'=> $lang->status,
                'options'=>$oDocumentModel->getStatusNameList()
            ));

            return $grid;
        }

		/**
		 * Set a document module
		 * @return void
		 */
        function dispDocumentAdminConfig() {
            $oDocumentModel = &getModel('document');
            $config = $oDocumentModel->getDocumentConfig();
            Context::set('config',$config);

            // Set the template file
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('document_config');
        }

		/**
		 * Display a report list on the admin page
		 * @return void
		 */
        function dispDocumentAdminDeclared() {
            $grid = $this->getGrid();
            $grid->removeColumn('ipaddress');
            $grid->removeColumn('status');
            $sortIndex = Context::get('sort_index');
            if (empty($sortIndex)){
                $sortIndex = 'document_declared.declared_count';
            }
            $sortOrder = Context::get('sort_order');
            if (empty($sortOrder)){
                $sortOrder = 'desc';
            }
            $grid->setSortIndex($sortIndex);
            $grid->setSortOrder($sortOrder);
			// option for a list
            $args = new stdClass();
			$args->page = Context::get('page'); // /< Page
			$args->list_count = 30; // /< the number of posts to display on a single page
			$args->page_count = 10; // /< the number of pages that appear in the page navigation

			//$args->sort_index = 'document_declared.declared_count'; // /< sorting values
            $args->sort_index = $grid->getSortIndex();
			//$args->order_type = 'desc'; // /< sorting values by order
            $args->order_type = $grid->getSortOrder();

			// get Status name list
			$oDocumentModel = &getModel('document');
			$statusNameList = $oDocumentModel->getStatusNameList();

			// get a list
			$declared_output = executeQuery('document.getDeclaredList', $args);
			if($declared_output->data && count($declared_output->data)) {
				$document_list = array();

				foreach($declared_output->data as $key => $document) {
                    $declared_output->data[$key]->mass_select_value = $document->document_srl;
                    $declared_output->data[$key]->url = getUrl('','document_srl',$document->document_srl);
					//$document_list[$key] = new documentItem();
					//$document_list[$key]->setAttribute($document);
				}
				//$declared_output->data = $document_list;
			}

			// Set values of document_model::getDocumentList() objects for a template
			Context::set('total_count', $declared_output->total_count);
			Context::set('total_page', $declared_output->total_page);
			Context::set('page', $declared_output->page);
			Context::set('document_list', $declared_output->data);
			Context::set('page_navigation', $declared_output->page_navigation);
            Context::set('status_name_list', $statusNameList);

            $grid->setRows($declared_output->data);
            Context::set('grid', $grid);

			// Set the template
			$this->setTemplatePath($this->module_path.'tpl');
			$this->setTemplateFile('declared_list');
        }

		/**
		 * Display a alias list on the admin page
		 * @return void
		 */
        function dispDocumentAdminAlias() {
            $args->document_srl = Context::get('document_srl');
            if(!$args->document_srl) return $this->dispDocumentAdminList();

            $oModel = &getModel('document');
            $oDocument = $oModel->getDocument($args->document_srl);
            if(!$oDocument->isExists()) return $this->dispDocumentAdminList();
            Context::set('oDocument', $oDocument);

            $output = executeQueryArray('document.getAliases', $args);
            if(!$output->data)
            {
                $aliases = array();
            }
            else
            {
                $aliases = $output->data; 
            }

            Context::set('aliases', $aliases);
	
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('document_alias');
        }
    }
?>
