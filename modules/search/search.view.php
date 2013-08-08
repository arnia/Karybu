<?php
    /**
     * @class  searchView
     * @author NHN (developers@xpressengine.com)
     * @brief view class of the search module
     *
     * Search Output
     *
     **/

    class searchView extends search {

        var $target_mid = array();
        var $skin = 'default';

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief Search Result
         **/
        function IS() {
            $oFile = &getClass('file');
            $oModuleModel = getModel('module');
            // Check permissions
            if(!$this->grant->access) return new Object(-1,'msg_not_permitted');

            $config = $oModuleModel->getModuleConfig('search');
            if(!$config->skin) $config->skin = 'default';
            Context::set('module_info', unserialize($config->skin_vars));
            $this->setTemplatePath($this->module_path."/skins/".$config->skin."/");

            $target = $config->target;
            if(!$target) $target = 'include';
			
			if (empty($config->target_module_srl))
				$module_srl_list = array();
			else
	            $module_srl_list = explode(',',$config->target_module_srl);

            // Set a variable for search keyword
            $is_keyword = Context::get('is_keyword');
            // Set page variables
            $page = (int)Context::get('page');
            if(!$page) $page = 1;
            // Search by search tab
            $where = Context::get('where');

            $isController = getController('search');
            // Create integration search model object
            if($is_keyword) {
                $oIS = getModel('search');
                switch($where) {
                    case 'document' :
                            $perPage = 10;
                            $search_target = Context::get('search_target');
                            if(!in_array($search_target, array('title','content','title_content','tag'))) $search_target = 'title';
                            Context::set('search_target', $search_target);
                            $output = $oIS->getDocuments($target, $module_srl_list, $search_target, $is_keyword, $page, $perPage);
                            //pagination
                            $total = $isController->countDocuments($is_keyword, $search_target);
                            $total_count = $output->total_count = $total;
                            $total_page = ($p = (int) ($total / $perPage)) + ($total - $p ? 1 : 0); unset($p);
                            $output->page_navigation = new PageHandler($total_count, $total_page, $page, $perPage);
                            Context::set('output', $output);
                            $this->setTemplateFile("document", $page);
                        break;
                    case 'comment' :
                            $perPage = 10;
                            $output = $oIS->getComments($target, $module_srl_list, $is_keyword, $page, $perPage);
                            //pagination
                            $total = $isController->countComments($is_keyword);
                            $total_count = $output->total_count = $total;
                            $total_page = ($p = (int) ($total / $perPage)) + ($total - $p ? 1 : 0); unset($p);
                            $output->page_navigation = new PageHandler($total_count, $total_page, $page, $perPage);
                            Context::set('output', $output);
                            $this->setTemplateFile("comment", $page);
                        break;
                    case 'trackback' :
                            $search_target = Context::get('search_target');
                            if(!in_array($search_target, array('title','url','blog_name','excerpt'))) $search_target = 'title';
                            Context::set('search_target', $search_target);
                            $output = $oIS->getTrackbacks($target, $module_srl_list, $search_target, $is_keyword, $page, 10);
                            Context::set('output', $output);
                            $this->setTemplateFile("trackback", $page);
                        break;
                    case 'multimedia' :
                            $output = $oIS->getImages($target, $module_srl_list, $is_keyword, $page,20);
                            Context::set('output', $output);
                            $this->setTemplateFile("multimedia", $page);
                        break;
                    case 'file' :
                            $output = $oIS->getFiles($target, $module_srl_list, $is_keyword, $page, 20);
                            Context::set('output', $output);
                            $this->setTemplateFile("file", $page);
                        break;
                    default :
                            $output['comment'] = $oIS->getComments($target, $module_srl_list, $is_keyword, $page, 5);

                            $perPage = 5;

                            //comment count
                            $total = $isController->countComments($is_keyword);
                            $output['comment']->total_count = $total;
                            $total_page = ($p = (int) ($total / $perPage)) + ($total - $p ? 1 : 0); unset($p);
                            $output['comment']->page_navigation = new PageHandler($total, $total_page, $page, $perPage);

                            $defaultDocumentSearchTarget = 'title';
                            $output['document'] = $oIS->getDocuments($target, $module_srl_list, $defaultDocumentSearchTarget, $is_keyword, $page, 5);

                            //document count
                            $total = $isController->countDocuments($is_keyword, $defaultDocumentSearchTarget);
                            $output['document']->total_count = $total;
                            $total_page = ($p = (int) ($total / $perPage)) + ($total - $p ? 1 : 0); unset($p);
                            $output['document']->page_navigation = new PageHandler($total, $total_page, $page, $perPage);

                            $output['trackback'] = $oIS->getTrackbacks($target, $module_srl_list, 'title', $is_keyword, $page, 5);
                            $output['multimedia'] = $oIS->getImages($target, $module_srl_list, $is_keyword, $page, 5);
                            $output['file'] = $oIS->getFiles($target, $module_srl_list, $is_keyword, $page, 5);
                            Context::set('search_result', $output);
							Context::set('search_target', 'title');
                            $this->setTemplateFile("index", $page);
                        break;
                }
            } else {
                $this->setTemplateFile("no_keywords");
            }

			$security = new Security();
			$security->encodeHTML('is_keyword', 'search_target', 'where', 'page');
        }

    }
?>
