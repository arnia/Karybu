<?php
    /**
     * @class  editorAPI
     * @author Arnia (dev@karybu.org)
     * @brief 
     **/

    class editorAPI extends editor {
        function dispEditorSkinColorset(&$oModule) {
            $oModule->add('colorset', Context::get('colorset'));
        }
    }
?>
