<?php
	/**
	 * commentAdminView class
	 * admin view class of the comment module
	 *
	 * @author Arnia (dev@karybu.org)
	 * @package /modules/comment
	 * @version 0.1
	 */
    class commentAdminView extends comment {
		/**
		 * Initialization
		 * @return void
		 */
        function init() {
        }
        function getGrid(){
            global $lang;
            $grid = new \Karybu\Grid\Backend();
            $grid->setId('commentListTable');
            $grid->setMassSelectName('cart');
            $grid->addColumn('content', 'link', array(
                'index' => 'content',
                'header'=> $lang->comment,
                'link_key'=>'url',
                'target'=>'_blank',
                'length'=>'40'
            ));
            $grid->addColumn('nick_name', 'member', array(
                'index' => 'nick_name',
                'header'=> $lang->nick_name,
                'masked'=>true,
                'title'=>'Info',
                'member_key'=>'member_srl'
            ));
            $grid->addColumn('declared_count', 'number', array(
                'index' => 'declared_count',
                'sort_index'=>'comment_declared.declared_count',
                'header'=> $lang->cmd_declare,
            ));
            $grid->addColumn('voted_count', 'number', array(
                'index' => 'voted_count',
                'header'=> '(+)',
            ));
            $grid->addColumn('blamed_count', 'number', array(
                'index' => 'blamed_count',
                'header'=> '(-)',
            ));
            $grid->addColumn('regdate', 'date', array(
                'index' => 'regdate',
                'header'=> $lang->date,
                'format'=>"Y-m-d\nH:i:s"
            ));
            $grid->addColumn('ipaddress', 'text', array(
                'index' => 'ipaddress',
                'header'=> $lang->ipaddress,
            ));
            $oCommentModel = &getModel("comment");
            $grid->addColumn('is_secret', 'options', array(
                'index' => 'is_secret',
                'header'=> $lang->status,
                'options'=>$oCommentModel->getSecretNameList()
            ));
            $grid->addColumn('status', 'options', array(
                'index' => 'status',
                'header'=> $lang->published,
                'options'=>array(
                    '0'=>$lang->published_name_list['N'],
                    '1'=>$lang->published_name_list['Y']
                )
            ));
            return $grid;
        }
		/**
		 * Display the list(for administrators)
		 * @return void
		 */
        function dispCommentAdminList() {
            // option to get a list
            $grid = $this->getGrid();
            $grid->removeColumn('declared_count');
            $args = new stdClass();
            $args->page = Context::get('page'); // /< Page
            $args->list_count = 10; // / the number of postings to appear on a single page
            $args->page_count = 5; // / the number of pages to appear on the page navigation

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
            $args->module_srl = Context::get('module_srl');
			/*
			$search_target = Context::get('search_target');
			$search_keyword = Context::get('search_keyword');
			if ($search_target == 'is_published' && $search_keyword == 'Y')
			{
				$args->status = 1;
			}
			if ($search_target == 'is_published' && $search_keyword == 'N')
			{
				$args->status = 0;
			}
			*/
				
            // get a list by using comment->getCommentList. 
            $oCommentModel = &getModel('comment');
			$secretNameList = $oCommentModel->getSecretNameList();
			$columnList = array('comment_srl', 'document_srl', 'is_secret', 'status', 'content', 'comments.member_srl', 'comments.nick_name', 'comments.regdate', 'ipaddress', 'voted_count', 'blamed_count');
            $output = $oCommentModel->getTotalCommentList($args, $columnList);
            foreach ($output->data as $key=>$comment){
                $output->data[$key]->url = getUrl('','document_srl',$comment->document_srl).'#comment_'.$comment->comment_srl;
                $output->data[$key]->mass_select_value = $comment->comment_srl;
            }
			
			$oCommentModel = &getModel("comment");
			$modules = $oCommentModel->getDistinctModules();
			$modules_list = $modules;
			
            // set values in the return object of comment_model:: getTotalCommentList() in order to use a template.
            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
            Context::set('comment_list', $output->data);
            Context::set('modules_list', $modules_list);
            Context::set('page_navigation', $output->page_navigation);
            Context::set('secret_name_list', $secretNameList);
            $grid->setRows($output->data);
            $grid->setTotalCount($output->total_count);
            $grid->setTotalPages($output->total_page);
            $grid->setCurrentPage($output->page);
            Context::set('grid', $grid);

            // set the template 
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('comment_list');
        }

		/**
		 * Show the blacklist of comments in the admin page
		 * @return void
		 */
        function dispCommentAdminDeclared() {
            // option to get a blacklist
            $grid = $this->getGrid();
            $grid->setShowOrderNumberColumn(true);
            $grid->removeColumn('voted_count');
            $grid->removeColumn('blamed_count');
            $grid->removeColumn('is_secret');
            $grid->removeColumn('status');
            $args = new stdClass();
            $args->page = Context::get('page'); // /< Page
            $args->list_count = 30; // /< the number of comment postings to appear on a single page
            $args->page_count = 10; // /< the number of pages to appear on the page navigation

            $args->sort_index = 'comment_declared.declared_count'; // /< sorting values
            $args->order_type = 'desc'; // /< sorted value


            $sortIndex = Context::get('sort_index');
            if (!$sortIndex){
                $sortIndex = 'comment_declared.declared_count';
            }
            $grid->setSortIndex($sortIndex);
            $args->sort_index = $grid->getSortIndex();
            $sortOrder = Context::get('sort_order');
            if (empty($sortOrder)){
                $sortOrder = 'desc';
            }
            $grid->setSortOrder($sortOrder);
            $args->sort_order = $grid->getSortOrder();


            // get a list
            $declared_output = executeQuery('comment.getDeclaredList', $args);

            if($declared_output->data && count($declared_output->data)) {
                $comment_list = array();

                $oCommentModel = &getModel('comment');
                foreach ($declared_output->data as $key=>$comment){
                    $declared_output->data[$key]->url = getUrl('','document_srl',$comment->document_srl).'#comment_'.$comment->comment_srl;
                    $declared_output->data[$key]->mass_select_value = $comment->comment_srl;
                }
                //$declared_output->data = $comment_list;
            }
        
            // set values in the return object of comment_model:: getCommentList() in order to use a template.
            Context::set('total_count', $declared_output->total_count);
            Context::set('total_page', $declared_output->total_page);
            Context::set('page', $declared_output->page);
            Context::set('comment_list', $declared_output->data);
            Context::set('page_navigation', $declared_output->page_navigation);
            $grid->setRows($declared_output->data);
            Context::set('grid', $grid);
            // set the template
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('declared_list');
        }
    }
?>
