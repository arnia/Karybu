<?php
    /**
     * @class  search
     * @author NHN (developers@xpressengine.com)
     * @brief view class of the search module
     **/

    class search extends ModuleObject {

        /**
         * @brief Implement if additional tasks are necessary when installing
         **/
        function moduleInstall() {
            // Registered in action forward
            $oModuleController = getController('module');
            $oModuleController->insertActionForward('search', 'view', 'IS');

            //Zend_Search_Lucene search index building
            $oModuleController->insertTrigger('document.insertDocument', 'search', 'controller', 'triggerInsertDocument', 'after');
            $oModuleController->insertTrigger('document.updateDocument', 'search', 'controller', 'triggerUpdateDocument', 'after');
            $oModuleController->insertTrigger('document.deleteDocument', 'search', 'controller', 'triggerDeleteDocument', 'after');
            $oModuleController->insertTrigger('comment.insertComment', 'search', 'controller', 'triggerInsertComment', 'after');
            $oModuleController->insertTrigger('comment.updateComment', 'search', 'controller', 'triggerUpdateComment', 'after');
            $oModuleController->insertTrigger('comment.deleteComment', 'search', 'controller', 'triggerDeleteComment', 'after');
            $oModuleController->insertTrigger('trackback.insertTrackback', 'search', 'controller', 'triggerInsertTrackback', 'after');
            $oModuleController->insertTrigger('trackback.deleteTrackback', 'search', 'controller', 'triggerDeleteTrackback', 'after');

            return new Object();
        }

        /**
         * @brief a method to check if successfully installed
         **/
        function checkUpdate() {
            $oDB = DB::getInstance();
            $oModuleModel = getModel('module');
            if(!$oModuleModel->getActionForward('IS')) return true;
            if(!$oModuleModel->getTrigger('document.insertDocument', 'search', 'controller', 'triggerInsertDocument', 'after')) return true;
            if(!$oModuleModel->getTrigger('document.updateDocument', 'search', 'controller', 'triggerUpdateDocument', 'after')) return true;
            if(!$oModuleModel->getTrigger('document.deleteDocument', 'search', 'controller', 'triggerDeleteDocument', 'after')) return true;
            if(!$oModuleModel->getTrigger('comment.insertComment', 'search', 'controller', 'triggerInsertComment', 'after')) return true;
            if(!$oModuleModel->getTrigger('comment.updateComment', 'search', 'controller', 'triggerUpdateComment', 'after')) return true;
            if(!$oModuleModel->getTrigger('comment.deleteComment', 'search', 'controller', 'triggerDeleteComment', 'after')) return true;
            if(!$oModuleModel->getTrigger('trackback.insertTrackback', 'search', 'controller', 'triggerInsertTrackback', 'after')) return true;
            if(!$oModuleModel->getTrigger('trackback.deleteTrackback', 'search', 'controller', 'triggerDeleteTrackback', 'after')) return true;
            return false;
        }

        /**
         * @brief Execute update
         **/
        function moduleUpdate() {
            $oDB = DB::getInstance();
            $oModuleModel = getModel('module');
            $oModuleController = getController('module');

            if(!$oModuleModel->getActionForward('IS')) {
                $oModuleController->insertActionForward('search', 'view', 'IS');
            }
            if (!$oModuleModel->getTrigger('document.insertDocument', 'search', 'controller', 'triggerInsertDocument', 'after')) $oModuleController->insertTrigger('document.insertDocument', 'search', 'controller', 'triggerInsertDocument', 'after');
            if (!$oModuleModel->getTrigger('document.updateDocument', 'search', 'controller', 'triggerUpdateDocument', 'after')) $oModuleController->insertTrigger('document.updateDocument', 'search', 'controller', 'triggerUpdateDocument', 'after');
            if (!$oModuleModel->getTrigger('document.deleteDocument', 'search', 'controller', 'triggerDeleteDocument', 'after')) $oModuleController->insertTrigger('document.deleteDocument', 'search', 'controller', 'triggerDeleteDocument', 'after');
            if (!$oModuleModel->getTrigger('comment.insertComment', 'search', 'controller', 'triggerInsertComment', 'after')) $oModuleController->insertTrigger('comment.insertComment', 'search', 'controller', 'triggerInsertComment', 'after');
            if (!$oModuleModel->getTrigger('comment.updateComment', 'search', 'controller', 'triggerUpdateComment', 'after')) $oModuleController->insertTrigger('comment.updateComment', 'search', 'controller', 'triggerUpdateComment', 'after');
            if (!$oModuleModel->getTrigger('comment.deleteComment', 'search', 'controller', 'triggerDeleteComment', 'after')) $oModuleController->insertTrigger('comment.deleteComment', 'search', 'controller', 'triggerDeleteComment', 'after');
            if (!$oModuleModel->getTrigger('trackback.insertTrackback', 'search', 'controller', 'triggerInsertTrackback', 'after')) $oModuleController->insertTrigger('trackback.insertTrackback', 'search', 'controller', 'triggerInsertTrackback', 'after');
            if (!$oModuleModel->getTrigger('trackback.deleteTrackback', 'search', 'controller', 'triggerDeleteTrackback', 'after')) $oModuleController->insertTrigger('trackback.deleteTrackback', 'search', 'controller', 'triggerDeleteTrackback', 'after');

            return new Object(0, 'success_updated');
        }

        /**
         * @brief Re-generate the cache file
         **/
        function recompileCache() {
        }
    }
?>
