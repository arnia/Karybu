<?php
    /**
     * @class  integrationModel
     * @author NHN (developers@xpressengine.com)
     * @brief Model class of integration module
     **/

    class searchModel extends module {
        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief Search documents
         **/
        function getDocuments($target, $module_srls_list, $search_target, $search_keyword, $page=1, $list_count = 20, $documentSerials=null) {
            if(is_array($module_srls_list)) $module_srls_list = implode(',',$module_srls_list);
            $oDocumentModel = getModel('document');

            $args = new stdClass();
            if($target == 'exclude') {
                $module_srls_list .= ',0'; // exclude 'trash'
                if ($module_srls_list{0} == ',') $module_srls_list = substr($module_srls_list, 1);
                $args->exclude_module_srl = $module_srls_list;
            } else {
                $args->module_srl = $module_srls_list;
                $args->exclude_module_srl = '0'; // exclude 'trash'
            }

            $args->page = $page;
            $args->list_count = $list_count;
            $args->page_count = 10;
            $args->search_target = $search_target;
            $args->search_keyword = $search_keyword;
            $args->sort_index = 'list_order';
            $args->order_type = 'asc';
            $args->statusList = array('PUBLIC');
            if (!$args->module_srl) unset($args->module_srl);

            // Get a list of documents
            if ($this->useLucene()) {
                $listCount = $args->list_count ? $args->list_count : 20;
                $documentSerials = $this->getDocumentSerials($search_keyword, $search_target, $page, $listCount);
                if ($documentSerials instanceof Exception) {
                    Context::set('query_error', $documentSerials->getMessage());
                }
                if (empty($documentSerials) || $documentSerials instanceof Exception) {
                    //create empty object compatible with the template
                    $output = new Object();
                    $output->data = array();
                    return $output;
                }
                $args->sort_index = 'list_order';
                $args->page = null;
                $args->list_count = $listCount;
                $args->page_count = $args->page_count?$args->page_count:10;
                $args->s_module_srl = $args->module_srl;
                $args->documents_srls = $documentSerials;
                $output = executeQuery('search.getSearchedDocumentsBySrls', $args);

                //remove indexed comments that no longer exist
                $isController = getController('search');
                $resultingSerials = array();
                foreach ($output->data as $o) $resultingSerials[] = $o->document_srl;
                $serialsToBeRemovedFromIndex = array_diff($documentSerials, $resultingSerials);
                foreach ($serialsToBeRemovedFromIndex as $srl) $isController->deleteDocumentFromIndex($srl);

                foreach($output->data as $key => $attribute) {
                    $oDocument = new documentItem();
                    $oDocument->setAttribute($attribute, false);
                    $output->data[$key] = $oDocument;
                }
            }
            else $output = $oDocumentModel->getDocumentList($args);

            foreach ($output->data as &$o) {
                $o->highlightedTitle = $this->highlight($search_keyword, $o->getTitle());
                $o->highlightedSummary = $this->highlight($search_keyword, $o->getSummary(200));
            }

            return $output;
        }

        function getDocumentSerials($searchQuery, $searchTarget, $page=1, $itemsPerPage=5)
        {
            $isController = getController('search');
            $documents = $isController->retrieveDocumentsFromIndex($searchQuery, $searchTarget, ($page-1) * $itemsPerPage, $itemsPerPage);
            if ($documents instanceof Exception) return $documents;
            $serials = array();
            foreach ($documents as $i=>$document) $serials[$i] = $document->srl;
            return $serials;
        }

        /**
         * @brief Comment Search
         **/
        function getComments($target, $module_srls_list, $search_keyword, $page=1, $list_count = 20, $commentSerials=null) {
            if(is_array($module_srls_list)){
                if (count($module_srls_list) > 0) $module_srls = implode(',',$module_srls_list);
            }
            else {
                $module_srls = ($module_srls_list)?$module_srls_list:0;
            }
            $args = new stdClass();
            if($target == 'exclude') $args->exclude_module_srl = $module_srls;
            else $args->module_srl = $module_srls;

            $args->page = $page;
            $args->list_count = $list_count;
            $args->page_count = 10;
            $args->search_target = 'content';
            $args->search_keyword = $search_keyword;
            $args->sort_index = 'list_order';
            $args->order_type = 'asc';

            // Get a list of documents
            $oCommentModel = getModel('comment');
            $commentSerials = null;
            if ($this->useLucene()) {
                $listCount = $args->list_count ? $args->list_count : 20;
                $commentSerials = $this->getCommentSerials($search_keyword, $page, $listCount);
                if ($commentSerials instanceof Exception) Context::set('query_error', $commentSerials->getMessage());
                if (empty($commentSerials) || $commentSerials instanceof Exception) {
                    //returns no results
                    $output = new Object();
                    $output->data = array();
                    return $output;
                }
                $args->sort_index = 'list_order';
                //we need to remove the page number from this query
                $args->page = null;
                $args->list_count = $listCount;
                $args->page_count = $args->page_count?$args->page_count:10;
                $args->s_module_srl = $args->module_srl;
                $args->comments_srls = $commentSerials;
                $output = executeQuery('search.getSearchedCommentsBySrls', $args);
                //remove indexed comments that no longer exist
                $isController = getController('search');
                $resultingSerials = array();
                foreach ($output->data as $o) $resultingSerials[] = $o->comment_srl;
                $serialsToBeRemovedFromIndex = array_diff($commentSerials, $resultingSerials);
                foreach ($serialsToBeRemovedFromIndex as $srl) {
                    $isController->deleteCommentFromIndex($srl);
                }
            }
            else $output = $oCommentModel->getTotalCommentList($args);

            if(!$output->toBool()|| !$output->data) return $output;
            foreach($output->data as $key => $val) {
                unset($_oComment);
                $_oComment = new CommentItem(0);
                $_oComment->setAttribute($val);
                if ($_oComment->variables['status'] == 2) {
                    unset($output->data[$key]);
                }
                else $output->data[$key] = $_oComment;
            }

            foreach ($output->data as &$o) {
                $o->highlightedSummary = $this->highlight($search_keyword, $o->getSummary(400));
            }

            return $output;
        }

        function getCommentSerials($searchQuery, $page=1, $itemsPerPage=5)
        {
            $isController = getController('search');
            $comments = $isController->retrieveCommentsFromIndex($searchQuery, ($page-1) * $itemsPerPage, $itemsPerPage);
            if ($comments instanceof Exception) return $comments;
            $serials = array();
            foreach ($comments as $i=>$comment) $serials[$i] = $comment->srl;
            return $serials;
        }

        /**
         * Depends on the state of the checkbox in Search admin
         * @return bool
         */
        function useLucene()
        {
            $oModuleModel = getModel('module');
            $config = $oModuleModel->getModuleConfig('search');
            return $config->lucene_search == 'Y' ? true : false;
        }

        /**
         *
         * @param $what String to highlight in Text
         * @param $where Text
         * @return string Text with $what highlighted
         */
        function highlight($what, $where)
        {
            /*require_once(_KARUBU_PATH_ . 'libs/Zend/Zend_Search_Lucene_includer.php');
            $query = Zend_Search_Lucene_Search_QueryParser::parse($what);
            return $query->highlightMatches($where);*/
            /*$doc = Zend_Search_Lucene_Document_Html::loadHTML($where);
            return $doc->highlight($what);*/
            return preg_replace('/' . preg_quote($what, '/') . '/i', "<span style='background-color: #fafad2'>$what</span>", $where);
        }

        /**
         * @brief Search trackbacks
         **/
        function getTrackbacks($target, $module_srls_list, $search_target = "title", $search_keyword, $page=1, $list_count = 20) {
            if(is_array($module_srls_list)) $module_srls = implode(',',$module_srls_list);
            else $module_srls = $module_srls_list;
            $args = new stdClass();
            if($target == 'exclude') $args->exclude_module_srl = $module_srls;
            else $args->module_srl = $module_srls;
            $args->page = $page;
            $args->list_count = $list_count;
            $args->page_count = 10;
            $args->search_target = $search_target;
            $args->search_keyword = $search_keyword;
            $args->sort_index = 'list_order'; 
            $args->order_type = 'asc';
            // Get a list of documents
            $oTrackbackModel = getAdminModel('trackback');
            $output = $oTrackbackModel->getTotalTrackbackList($args);
            if(!$output->toBool()|| !$output->data) return $output;
            return $output;
        }

        /**
         * @brief File Search
         **/
        function _getFiles($target, $module_srls_list, $search_keyword, $page, $list_count, $direct_download = 'Y') {
            if(is_array($module_srls_list)) $module_srls = implode(',',$module_srls_list);
            else $module_srls = $module_srls_list;
            if($target == 'exclude') $args->exclude_module_srl = $module_srls;
            else $args->module_srl = $module_srls;
            $args->page = $page;
            $args->list_count = $list_count;
            $args->page_count = 10;
            $args->search_target = 'filename';
            $args->search_keyword = $search_keyword;
            $args->sort_index = 'files.file_srl'; 
            $args->order_type = 'desc';
            $args->isvalid = 'Y';
            $args->direct_download = $direct_download=='Y'?'Y':'N';
            // Get a list of documents
            $oFileAdminModel = getAdminModel('file');
            $output = $oFileAdminModel->getFileList($args);
            if(!$output->toBool() || !$output->data) return $output;

            $list = array();
            foreach($output->data as $key => $val) {
                $obj = null;
                $obj->filename = $val->source_filename;
                $obj->download_count = $val->download_count;
                if(substr($val->download_url,0,2)=='./') $val->download_url = substr($val->download_url,2);
                $obj->download_url = Context::getRequestUri().$val->download_url;
                $obj->target_srl = $val->upload_target_srl;
                $obj->file_size = $val->file_size;
                // Images
                if(preg_match('/\.(jpg|jpeg|gif|png)$/i', $val->source_filename)) {
                    $obj->type = 'image';

                    $thumbnail_path = sprintf('files/cache/thumbnails/%s',getNumberingPath($val->file_srl, 3));
                    if(!is_dir($thumbnail_path)) FileHandler::makeDir($thumbnail_path);
                    $thumbnail_file = sprintf('%s%dx%d.%s.jpg', $thumbnail_path, 120, 120, 'crop');
                    $thumbnail_url  = Context::getRequestUri().$thumbnail_file;
                    if(!file_exists($thumbnail_file)) FileHandler::createImageFile($val->uploaded_filename, $thumbnail_file, 120, 120, 'jpg', 'crop');
                    $obj->src = sprintf('<img src="%s" alt="%s" width="%d" height="%d" />', $thumbnail_url, htmlspecialchars($obj->filename), 120, 120);
                // Videos
                } elseif(preg_match('/\.(swf|flv|wmv|avi|mpg|mpeg|asx|asf|mp3)$/i', $val->source_filename)) {
                    $obj->type = 'multimedia';
                    $obj->src = sprintf('<script type="text/javascript">displayMultimedia("%s",120,120);</script>', $obj->download_url);
                // Others
                } else {
                    $obj->type = 'binary';
                    $obj->src = '';
                }

                $list[] = $obj;
                $target_list[] = $val->upload_target_srl;
            }
            $output->data = $list;

            $oDocumentModel = getModel('document');
            $document_list = $oDocumentModel->getDocuments($target_list);
            if($document_list) foreach($document_list as $key => $val) {
                foreach($output->data as $k => $v) {
                    if($v->target_srl== $val->document_srl) {
                        $output->data[$k]->url = $val->getPermanentUrl();
                        $output->data[$k]->regdate = $val->getRegdate("Y-m-d H:i");
                        $output->data[$k]->nick_name = $val->getNickName();
                    }
                }
            }

            $oCommentModel = getModel('comment');
            $comment_list = $oCommentModel->getComments($target_list);
            if($comment_list) foreach($comment_list as $key => $val) {
                foreach($output->data as $k => $v) {
                    if($v->target_srl== $val->comment_srl) {
                        $output->data[$k]->url = $val->getPermanentUrl();
                        $output->data[$k]->regdate = $val->getRegdate("Y-m-d H:i");
                        $output->data[$k]->nick_name = $val->getNickName();
                    }
                }
            }

            return $output;
        }

        /**
         * @brief Multimedia Search
         **/
        function getImages($target, $module_srls_list, $search_keyword, $page=1, $list_count = 20) {
            return $this->_getFiles($target, $module_srls_list, $search_keyword, $page, $list_count);
        }

        /**
         * @brief Search for attachments
         **/
        function getFiles($target, $module_srls_list, $search_keyword, $page=1, $list_count = 20) {
            return $this->_getFiles($target, $module_srls_list, $search_keyword, $page, $list_count, 'N');
        }

        function addAliases($documents){
            foreach($documents as $document){
                $document_srls[] = $document->document_srl;
            }
            $args = new stdClass();
            $args->document_srls = $document_srls;
            $output = executeQueryArray('search.getDocumentAliases',$args);
            $aliases = $output->data;
            foreach($documents as $document){
                foreach($aliases as $alias){
                    if($document->document_srl == $alias->document_srl){
                        $document->alias = $alias->alias_title;
                    }
                }
            }
        }

    }
?>
