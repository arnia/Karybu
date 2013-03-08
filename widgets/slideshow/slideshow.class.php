<?php

    class slideshow extends WidgetHandler {

        function proc($args) {

            $imgCations = array();
            $imgUrls = array();
            $imgSrcs = array();

            if ($args->image1 != null) {
                array_push($imgSrcs, $args->image1);
            }
            if ($args->image2 != null) {
                array_push($imgSrcs, $args->image2);
            }
            if ($args->image3 != null) {
                array_push($imgSrcs, $args->image3);
            }
            if ($args->image4 != null) {
                array_push($imgSrcs, $args->image4);
            }
            if ($args->image5 != null) {
                array_push($imgSrcs, $args->image5);
            }

            array_push($imgUrls, $args->link1);
            array_push($imgUrls, $args->link2);
            array_push($imgUrls, $args->link3);
            array_push($imgUrls, $args->link4);
            array_push($imgUrls, $args->link5);

            array_push($imgCations, $args->caption1);
            array_push($imgCations, $args->caption2);
            array_push($imgCations, $args->caption3);
            array_push($imgCations, $args->caption4);
            array_push($imgCations, $args->caption5);

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