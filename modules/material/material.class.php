<?php
    /**
     * @class  material
     * @author Arnia (developers@xpresseinge.com)
     * @brief  material Module high class
     **/


    class material extends ModuleObject {

		function material(){
		}

		/**
		 * @brief Implementation of additional work required for installation at
		 **/
		function moduleInstall() {
            $oModuleController = &getController('module');

			$oModuleController->insertTrigger('material.deleteMaterial', 'file', 'controller', 'triggerDeleteAttached', 'after');
		}

		/**
		 * @brief Installation method to check that there are no more than
		 **/
		function checkUpdate() {
            $oModuleModel = &getModel('module');
            $oDB = DB::getInstance();
			
			if(!$oModuleModel->getTrigger('material.deleteMaterial', 'file', 'controller', 'triggerDeleteAttached', 'after')){
				return true;
			}

			if($oModuleModel->getTrigger('material.deleteMaterials', 'file', 'controller', 'triggerDeleteModuleFiles', 'after')){
				return true;
			}

			if($oDB->isColumnExists('material','module_srl')){
				return true;
			}

			return false;
		}

		/**
		 * @brief Run update
		 **/
		function moduleUpdate() {
			$oModuleModel = &getModel('module');
            $oModuleController = &getController('module');
            $oDB = DB::getInstance();

			if(!$oModuleModel->getTrigger('material.deleteMaterial', 'file', 'controller', 'triggerDeleteAttached', 'after')){
				$oModuleController->insertTrigger('material.deleteMaterial', 'file', 'controller', 'triggerDeleteAttached', 'after');
			}

			if($oModuleModel->getTrigger('material.deleteMaterials', 'file', 'controller', 'triggerDeleteModuleFiles', 'after')){
				$oModuleController->deleteTrigger('material.deleteMaterials', 'file', 'controller', 'triggerDeleteModuleFiles', 'after');
			}

			if($oDB->isColumnExists('material','module_srl')){
				if($oDB->isIndexExists("material", "idx_textyle_srl")) {
					$oDB->dropIndex("material", "idx_textyle");
				}

				$oDB->dropColumn('material','module_srl');
			}

			return new Object(0, 'success_updated');
		}

		/**
		 * @brief Recreate the cache file
		 **/
		function recompileCache() {
		}
    }
?>
