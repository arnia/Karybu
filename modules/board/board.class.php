<?php
    /**
     * @class  board
     * @author NHN (developers@xpressengine.com)
     * @brief  board module high class
     **/

    class board extends ModuleObject {

        var $search_option = array('title','content','title_content','comment','user_name','nick_name','user_id','tag'); ///< 검색 옵션

        var $order_target = array('list_order', 'update_order', 'regdate', 'voted_count', 'blamed_count', 'readed_count', 'comment_count', 'title'); // 정렬 옵션

        var $skin = "default"; ///< skin name
        var $list_count = 20; ///< the number of documents displayed in a page
        var $page_count = 10; ///< page number
        var $category_list = NULL; ///< category list


        /**
         * @brief install the module
         **/
        function moduleInstall() {
            // use action forward(enabled in the admin model)
            $oModuleController = &getController('module');
            $oModuleModel = &getModel('module');

            // 2007. 10. 17 insert member menu trigger
            $oModuleController->insertTrigger('member.getMemberMenu', 'board', 'controller', 'triggerMemberMenu', 'after');

            return new Object();
        }

        /**
         * @brief chgeck module method
         **/
        function checkUpdate() {
            $oModuleModel = &getModel('module');

            // 2007. 10. 17 get the member menu trigger
            if(!$oModuleModel->getTrigger('member.getMemberMenu', 'board', 'controller', 'triggerMemberMenu', 'after')) return true;

            // 2011. 09. 20 when add new menu in sitemap, custom menu add
            if(!$oModuleModel->getTrigger('menu.getModuleListInSitemap', 'board', 'model', 'triggerModuleListInSitemap', 'after')) return true;
            return false;
        }

        /**
         * @brief update module
         **/
        function moduleUpdate() {
            $oModuleModel = &getModel('module');
            $oModuleController = &getController('module');

            // 2007. 10. 17  check the member menu trigger, if it is not existed then insert
            if(!$oModuleModel->getTrigger('member.getMemberMenu', 'board', 'controller', 'triggerMemberMenu', 'after'))
                $oModuleController->insertTrigger('member.getMemberMenu', 'board', 'controller', 'triggerMemberMenu', 'after');

            // 2011. 09. 20 when add new menu in sitemap, custom menu add
            if(!$oModuleModel->getTrigger('menu.getModuleListInSitemap', 'board', 'model', 'triggerModuleListInSitemap', 'after'))
                $oModuleController->insertTrigger('menu.getModuleListInSitemap', 'board', 'model', 'triggerModuleListInSitemap', 'after');

            return new Object(0, 'success_updated');
        }

		function moduleUninstall() {
			$output = executeQueryArray("board.getAllBoard");
			if(!$output->data) return new Object();
			@set_time_limit(0);
			$oModuleController =& getController('module');
			foreach($output->data as $board)
			{
				$oModuleController->deleteModule($board->module_srl);
			}
			return new Object();
		}

        /**
         * @brief re-generate the cache files
         **/
        function recompileCache() {
        }

    }
?>
