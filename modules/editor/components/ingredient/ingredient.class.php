<?php
    /**
     * @class  ingredient
     * @author ARNIA
     * @brief ingredient components
     **/

    class ingredient extends EditorHandler {
        // editor_sequence from the editor must attend mandatory wearing ....
        var $editor_sequence = 0;
        var $component_path = '';

        /**
         * @brief editor_sequence and components out of the path
         **/
        function ingredient($editor_sequence, $component_path) {
            $this->editor_sequence = $editor_sequence;
            $this->component_path = $component_path;
        }

        /**
         * @brief popup window to display in popup window request is to add content
         **/
        function getPopupContent() {
            // Pre-compiled source code to compile template return to
            $tpl_path = $this->component_path.'tpl';
            $tpl_file = 'popup.html';
            $oTemplate = &TemplateHandler::getInstance();
            return $oTemplate->compile($tpl_path, $tpl_file);
        }

    }
?>
