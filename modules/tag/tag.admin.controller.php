<?php
    /**
     * @class  tagAdminController
     * @author Arnia (developers@xpressengine.com)
     * @brief admin controller class of the tag module
     **/

    class tagAdminController extends tag {
        /**
         * @brief Delete all tags for a particular module
         **/
        function deleteModuleTags($module_srl) {
            $args->module_srl = $module_srl;
            return executeQuery('tag.deleteModuleTags', $args);
        }
    }
?>
