<?php
    /**
     * @class  materialController
     * @author Arnia (developers@xpresseinge.com)
     * @brief  material 모듈의 Controller class
     **/

    class materialController extends material {

        var $thum = array('S'=>array('width'=>188));

        /**
         * @brief 초기화
         **/
        function init() {

        }

        function procMaterialInsert(){
            $var = Context::getRequestVars();
            if(!$var->auth || !$var->type) return new Object(-1,'msg_not_permitted');

            $oMaterialModel = &getModel('material');
            $member_srl = $oMaterialModel->getMemberSrlByAuth($var->auth);
            if(!$member_srl) return new Object(-1,'msg_invalid_request');

            if($var->type=='img'){
                if($var->image){
                    $path = sprintf('files/cache/material/tmp/%s/', getNumberingPath($member_srl));
                    $filename = basename($var->image);
                    $file = $path.$filename;
                    FileHandler::makeDir($path);
                    FileHandler::getRemoteFile($var->image, $file);

                    if(file_exists($file)) {
                        $material_srl = getNextSequence();
                        $ext = substr(strrchr($filename,'.'),1);
                        $ext = array_shift(explode('?',$ext));

                        // insert file module
                        $file_info = array();
                        $file_info['tmp_name'] = $file;
                        $file_info['name'] = sprintf("%s.%s",$material_srl,$ext);

                        $oFileController = &getController('file');
                        $output = $oFileController->insertFile($file_info, $member_srl,$material_srl,0,true);
                        if(!$output->toBool()) return $output;

                        //set File valid
                        $oFileController->setFilesValid($output->get('upload_target_srl'));

                        // delete temp file
                        FileHandler::removeFile($filename);

                        $uploaded_filename = $output->get('uploaded_filename');
                        $_filename = sprintf("%s%s.%%s.%s",preg_replace("/\/[^\/]*$/","/",$uploaded_filename),$material_srl,$ext);
                        $s_filename = sprintf($_filename,'S');

                        list($w,$h) = @getimagesize($uploaded_filename);

                        if($w > $this->thum['S']['width'] || $h > $this->thum['S']['height']){
                            FileHandler::createImageFile($uploaded_filename,$s_filename,$this->thum['S']['width'],$h,'','ratio');
                        }else{
                            FileHandler::copyFile($uploaded_filename,$s_filename);
                        }

                        // replace image src
                        $var->content = str_replace($var->image,$uploaded_filename,$var->content);
                    }else{
                        $var->image = null;
                    }
                }else{
                    return new Object(-1,'msg_not_select_image');
                }
            }

            // there is no file or copy failed
            if($var->type=='img' && !$var->image){
                return new Object(-1,'msg_fail_image_save');
            }
            $args = new stdClass();
            $args->material_srl = !empty($material_srl) ? $material_srl : getNextSequence();
            $args->member_srl = $member_srl;
            $args->type = $var->type;
            $args->content = $var->content;

            $output = executeQuery('material.insertMaterial', $args);
            return $output;
        }

        function procMaterialDelete(){
            $material_srl = Context::get('material_srl');
            if(!$material_srl) return new Object();
            return $this->deleteMaterial($material_srl);
        }

        function deleteMaterial($material_srl){
            if(strpos($material_srl, ',') === false) {
                $args->material_srl = $material_srl;
            } else {
                $args->material_srls = $material_srl;
            }
            $output = executeQuery('material.deleteMaterial', $args);

            // delete thumnail image
            $oFileModel = &getModel('file');
            $file_list = array();
            if(strpos(',', $material_srl) === false) {
                $file_list = $oFileModel->getFiles($material_srl);
            } else {
                $material_srls = explode(',', $material_srl);
                foreach($material_srls as $srl) {
                    $files = $oFileModel->getFiles($srl);
                    if($files) $file_list = array_merge($file_list, $files);
                }
            }

            if(count($file_list)) {
                foreach($file_list as $k => $file){
                    $ext = substr(strrchr($file->source_filename,'.'),1);
                    $_filename = sprintf("%s%s.%%s.%s",preg_replace("/\/[^\/]*$/","/",$file->uploaded_filename),$material_srl,$ext);
                    $s_filename = sprintf($_filename,'S');
                    $l_filename = sprintf($_filename,'L');
                    FileHandler::removeFile($s_filename);
                    FileHandler::removeFile($l_filename);
                }
            }

            $obj->document_srl = $material_srl;
            // trigger 호출 (after)
            if($output->toBool()) {
                $trigger_output = ModuleHandler::triggerCall('material.deleteMaterial', 'after', $obj);
                if(!$trigger_output->toBool()) {
                    return $trigger_output;
                }
            }
            return $output;
        }

        function insertMaterialAuth($member_srl){
            $args->member_srl = $member_srl;
            $output = executeQuery('material.deleteMaterialAuth',$args);

            $args->auth = substr(md5($member_srl . microtime() .rand()),0,32);
            $output = executeQuery('material.insertMaterialAuth',$args);
            return $output;
        }

        function deleteMaterialAuth($member_srl){
            $args->member_srl = $member_srl;

            $output = executeQuery('material.deleteMaterialAuth',$args);
            return $output;
        }

    }

?>
