<?php
    /**
     * @class  pollAdminView
     * @author Arnia (dev@karybu.org)
     * @brief The admin view class of the poll module
     **/

    class pollAdminView extends poll {

        /**
         * @brief Initialization
         **/
        function init() {
        }
        function getGrid(){
            global $lang;
            $grid = new \Karybu\Grid\Backend();
            $grid->setId('pollListTable');
            $grid->setMassSelectName('cart');
            $grid->addColumn('title', 'link', array(
                'index' => 'title',
                'header'=> $lang->title,
                'link_key'=>'url',
                'target'=>'_blank',
                'length'=>'40'
            ));
            $grid->addColumn('checkcount', 'options', array(
                'index' => 'checkcount',
                'header'=> $lang->poll_checkcount,
                'options'=>array(
                    '1'=>$lang->single_check,
                    '*'=>$lang->multi_check
                ),
            ));
            $grid->addColumn('poll_count', 'number', array(
                'index' => 'poll_count',
                'header'=> $lang->poll_join_count,
            ));
            $grid->addColumn('nick_name', 'member', array(
                'index' => 'nick_name',
                'header'=> $lang->author,
                'masked'=>true,
                'title'=>'Info',
                'member_key'=>'member_srl',
                'sort_index'=>'M.nick_name'
            ));
            $grid->addColumn('poll_regdate', 'date', array(
                'index' => 'poll_regdate',
                'header'=> $lang->date,
                'format'=>"Y-m-d\nH:i:s"
            ));
            $grid->addColumn('poll_stop_date', 'date', array(
                'index' => 'poll_stop_date',
                'header'=> $lang->poll_stop_date,
                'format'=>"Y-m-d"
            ));
            return $grid;
        }
        /**
         * @brief Administrator's Page
         **/
        function dispPollAdminList() {
            // Arrange the search options
            $args = new stdClass();
            $search_target = trim(Context::get('search_target'));
            $search_keyword = trim(Context::get('search_keyword'));

            if($search_target && $search_keyword) {
                switch($search_target) {
                    case 'title' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_title= $search_keyword;
                        break;
                    case 'regdate' :
                            $args->s_regdate = $search_keyword;
                        break;
                    case 'ipaddress' :
                            $args->s_ipaddress= $search_keyword;
                        break;
                }
            }
            // Options to get a list of pages
            $args->page = Context::get('page');
            $args->list_count = 20; // The number of posts to show on one page
            $args->page_count = 5; // The number of pages to display in the page navigation

            $grid = $this->getGrid();
            $sortIndex = Context::get('sort_index');
            $grid->setSortIndex($sortIndex);
            //$args->sort_index = 'list_order'; // /< Sorting values
            $args->sort_index = $grid->getSortIndex();
            if (empty($args->sort_index)){
                $args->sort_index = 'P.list_order';
            }
            $sortOrder = Context::get('sort_order');
            $grid->setSortOrder($sortOrder);
            $args->sort_order = $grid->getSortOrder();

            //$args->sort_index = 'P.list_order'; // Sorting value

            // Get the list
            $oPollAdminModel = &getAdminModel('poll');
            $output = $oPollAdminModel->getPollListWithMember($args);

			// check poll type. document or comment
			if(is_array($output->data))
			{
				$uploadTargetSrlList = array();
				foreach($output->data AS $key=>$value)
				{
					array_push($uploadTargetSrlList, $value->upload_target_srl);
				}

            	$oDocumentModel = &getModel('document');
				$targetDocumentOutput = $oDocumentModel->getDocuments($uploadTargetSrlList);
				if(!is_array($targetDocumentOutput)) $targetDocumentOutput = array();

				$oCommentModel = &getModel('comment');
				$columnList = array('comment_srl', 'document_srl');
				$targetCommentOutput = $oCommentModel->getComments($uploadTargetSrlList, $columnList);
				if(!is_array($targetCommentOutput)) $targetCommentOutput = array();

				foreach($output->data as $key=>$value)
				{
					if(array_key_exists($value->upload_target_srl, $targetDocumentOutput))
						$value->document_srl = $value->upload_target_srl;
                        $output->data[$key]->mass_select_value = $value->poll_index_srl;

					if(array_key_exists($value->upload_target_srl, $targetCommentOutput))
					{
						$value->comment_srl = $value->upload_target_srl;
						$value->document_srl = $targetCommentOutput[$value->comment_srl]->document_srl;
					}
                    $output->data[$key]->url = getUrl();
                    if (!empty($value->document_srl)){
                        $output->data[$key]->url .="?document_srl=".$value->document_srl;
                    }
                    if (!empty($value->comment_srl)){
                        $output->data[$key]->url .="#comment_".$value->comment_srl;
                    }
				}
			}

            // Configure the template variables
            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
            Context::set('poll_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);
            //Context::set('module_list', $module_list);
			
			$security = new Security();				
			$security->encodeHTML('poll_list..title');
            $grid->setRows($output->data);
            Context::set('grid', $grid);

            // Set a template
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('poll_list');
        }

        /**
         * @brief Confgure the poll skin and colorset
         **/
        function dispPollAdminConfig() {
            $oModuleModel = &getModel('module');
            // Get the configuration information
            $config = $oModuleModel->getModuleConfig('poll');
            Context::set('config', $config);
            // Get the skin information
            $skin_list = $oModuleModel->getSkins($this->module_path);
            Context::set('skin_list', $skin_list);

            if(!$skin_list[$config->skin]) $config->skin = "default";
            // Set the skin colorset once the configurations is completed
            Context::set('colorset_list', $skin_list[$config->skin]->colorset);
			
			$security = new Security();				
			$security->encodeHTML('config..');
			$security->encodeHTML('skin_list..title');
			$security->encodeHTML('colorset_list..name','colorset_list..title');
			
            // Set a template
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('config');
        }

        /**
         * @brief Poll Results
         **/
        function dispPollAdminResult() {
            // Popup layout
            $this->setLayoutFile("popup_layout");
            // Draw results
            $args->poll_srl = Context::get('poll_srl'); 
            $args->poll_index_srl = Context::get('poll_index_srl'); 

            $output = executeQuery('poll.getPoll', $args);
            if(!$output->data) return $this->stop('msg_poll_not_exists');
            $poll->stop_date = $output->data->stop_date;
            $poll->poll_count = $output->data->poll_count;

            $output = executeQuery('poll.getPollTitle', $args);
            if(!$output->data) return $this->stop('msg_poll_not_exists');

            $poll->poll[$args->poll_index_srl]->title = $output->data->title;
            $poll->poll[$args->poll_index_srl]->checkcount = $output->data->checkcount;
            $poll->poll[$args->poll_index_srl]->poll_count = $output->data->poll_count;

            $output = executeQuery('poll.getPollItem', $args);
            foreach($output->data as $key => $val) {
                $poll->poll[$val->poll_index_srl]->item[] = $val;
            }

            $poll->poll_srl = $poll_srl;

            Context::set('poll',$poll);
            // Configure the skin and the colorset for the default configuration
            $oModuleModel = &getModel('module');
            $poll_config = $oModuleModel->getModuleConfig('poll');
            Context::set('poll_config', $poll_config);

            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('result');
        }
    }
?>
