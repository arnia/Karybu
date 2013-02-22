<?php
    /**
     * @class  materialAPI
     * @author NHN (developers@xpresseinge.com)
     * @brief  material 모듈의 View Action에 대한 API 처리
     **/

    class materialAPI extends material {

        /**
         * @brief check alias
         **/
        function dispMaterialList(&$oModule) {
            $oModule->add('material_list',Context::get('material_list'));
            $oModule->add('page_navigation',Context::get('page_navigation'));
        }

    }
?>
