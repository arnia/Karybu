<?php

    class slideshow extends WidgetHandler {

        function proc($args) {

            $imgCations = array();
            $imgUrls = array();
            $imgSrcs = array();
            for ($i = 1; $i<=5;$i++) {
                $image = 'image'.$i;
                $link = 'link'.$i;
                $caption = 'caption'.$i;
                if (isset($args->$image)) {
                    array_push($imgSrcs, $args->$image);
                    if (isset($args->$link)) {
                        array_push($imgUrls, $args->$link);
                    }
                    else {
                        array_push($imgUrls, null);
                    }
                    if (isset($args->$caption)) {
                        array_push($imgCations, $args->$caption);
                    }
                    else {
                        array_push($imgCations, null);
                    }
                }
            }


            Context::set('urls', $imgUrls);
            Context::set('Imgs', $imgSrcs);
            Context::set('captions', $imgCations);

//            foreach ($output->data as $image) {
//            	if (in_array($image->filename, $imgSrcsGray)) $images[]=$image;
//            }
//            $imageNo = count($images);
//
//            // add images
//            $widget_info->info = array();
//            foreach($images as $i => $image){
//            	$widget_info->info[$i]['image'] = $image->filename;
//              $widget_info->info[$i]['imageColor'] = $image->filename;
//            	$widget_info->info[$i]['title'] = $image->attributes["title"];
//            	$widget_info->info[$i]['description'] = $image->attributes["description"];
//            	$widget_info->info[$i]['url'] = $image->attributes["url"];
//            }

            // set template path
            $tpl_path = sprintf('%sskins/%s', $this->widget_path, $args->skin);

            // set skin file
            $tpl_file = 'banner';

            // set skin
            $oTemplate = &TemplateHandler::getInstance();
            return $oTemplate->compile($tpl_path, $tpl_file);

        }

    }