<?php
/**
 * @class  image_link
 * @author Arnia (dev@karybu.org)
 * @brief Add an image, or to modify the properties of components
 **/

class image_link extends EditorHandler
{
    // editor_sequence from the editor must attend mandatory wearing ....
    var $editor_sequence = 0;
    var $component_path = '';

    /**
     * @brief editor_sequence and components out of the path
     **/
    function image_link($editor_sequence, $component_path)
    {
        $this->editor_sequence = $editor_sequence;
        $this->component_path = $component_path;
    }

    /**
     * @brief popup window to display in popup window request is to add content
     **/
    function getPopupContent()
    {
        // Pre-compiled source code to compile template return to
        $tpl_path = $this->component_path . 'tpl';
        $tpl_file = 'popup.html';

        Context::set("tpl_path", $tpl_path);

        $oTemplate = & TemplateHandler::getInstance();
        return $oTemplate->compile($tpl_path, $tpl_file);
    }

    /**
     * @brief Editor of the components separately if you use a unique code to the html code for a method to change
     *
     * Images and multimedia, seolmundeung unique code is required for the editor component added to its own code, and then
     * DocumentModule:: transContent() of its components transHtml() method call to change the html code for your own
     **/
    function transHTML($xml_obj)
    {
        $src = $xml_obj->attrs->src;
        if (isset($xml_obj->attrs->width)) {
            $width = $xml_obj->attrs->width;
        }
        if (isset($xml_obj->attrs->height)) {
            $height = $xml_obj->attrs->height;
        }
        if (isset($xml_obj->attrs->align)) {
            $align = $xml_obj->attrs->align;
        }
        if (isset($xml_obj->attrs->alt)) {
            $alt = $xml_obj->attrs->alt;
        }
        if (isset($xml_obj->attrs->title)) {
            $title = $xml_obj->attrs->title;
        }
        if (isset($xml_obj->attrs->border)) {
            $border = (int)$xml_obj->attrs->border;
        }
        if (isset($xml_obj->attrs->link_url)) {
            $link_url = $xml_obj->attrs->link_url;
        }
        if (isset($xml_obj->attrs->open_window)) {
            $open_window = $xml_obj->attrs->open_window;
        }
        $style = null;
        if (isset($xml_obj->attrs->style)) {
            $style = $xml_obj->attrs->style;
        }
        if (isset($xml_obj->attrs->margin)) {
            $margin = (int)$xml_obj->attrs->margin;
        }

        $src = str_replace(array('&', '"'), array('&amp;', '&qout;'), $src);
        $src = str_replace('&amp;amp;', '&amp;', $src);

        // Image containing the address to the address conversion request uri (rss output, etc. purposes)
        $temp_src = explode('/', $src);
        if (substr($src, 0, 2) == './') {
            $src = Context::getRequestUri() . substr($src, 2);
        } elseif (substr($src, 0, 1) == '/') {
            if ($_SERVER['HTTPS'] == 'on') {
                $http_src = 'https://';
            } else {
                $http_src = 'http://';
            }
            $src = $http_src . $_SERVER['HTTP_HOST'] . $src;
        } elseif (!strpos($temp_src[0], ':') && $src) {
            $src = Context::getRequestUri() . $src;
        }

        $attr_output = array();
        $attr_output = array("src=\"" . $src . "\"");
        if (isset($alt)) {
            $attr_output[] = "alt=\"" . $alt . "\"";
        }

        if (isset($title)) {
            $attr_output[] = "title=\"" . $title . "\"";
        }
        if (isset($margin)) {
            $style = trim(preg_replace('/margin[a-z\-]*[ ]*:[ ]*[0-9 a-z]+(;| )/i', '', $style)) . ';';
            $style = str_replace(';;', ';', $style);
            if ($style == ';') {
                $style = '';
            }
            $style .= ' margin:' . $margin . 'px;';
        }
        if (isset($align)) {
            $attr_output[] = "align=\"" . $align . "\"";
        }

        if (preg_match("/\.png$/i", $src)) {
            $attr_output[] = "class=\"iePngFix\"";
        }

        if (isset($width)) {
            $attr_output[] = 'width="' . $width . '"';
        }
        if (isset($height)) {
            $attr_output[] = 'height="' . $height . '"';
        }
        if (isset($border)) {
            $style = trim(preg_replace('/border[a-z\-]*[ ]*:[ ]*[0-9 a-z]+(;| )/i', '', $style)) . ';';
            $style = str_replace(';;', ';', $style);
            if ($style == ';') {
                $style = '';
            }
            $style .= ' border-style: solid; border-width:' . $border . 'px;';
        }

        $code = sprintf("<img %s style=\"%s\" />", implode(' ', $attr_output), $style);

        if (isset($link_url)) {
            if ($open_window == 'Y') {
                $code = sprintf(
                    '<a href="%s" onclick="window.open(this.href);return false;">%s</a>',
                    $link_url,
                    $code
                );
            } else {
                $code = sprintf('<a href="%s" >%s</a>', $link_url, $code);
            }
        }
        if (isset($code)) {
            return $code;
        }
    }

}
?>
