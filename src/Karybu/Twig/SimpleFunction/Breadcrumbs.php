<?php
namespace Karybu\Twig\SimpleFunction;
class Breadcrumbs extends \Twig_SimpleFunction{
    /**
     * @var member var used for cacheing
     */
    protected $_breadcrumbs = null;

    /**
     * constructor
     * @param $name
     * @param $callable
     * @param array $options
     */
    public function __construct(){
        parent::__construct('breadcrumbs', array($this, 'getBreadcrumbs'));
    }

    /**
     * get the html for breadcrumbs
     * @param null $currentPage
     * @param null $menu
     * @param string $separator
     * @param null $homeText
     * @return member|null|string
     */
    public function getBreadcrumbs($currentPage = null, $menu = null, $separator = ' &raquo; ', $homeText = null){
        if (is_null($this->_breadcrumbs)){
            if (is_null($currentPage)){
                $this->_breadcrumbs = '';
                return $this->_breadcrumbs;
            }
            $path = array();
            if (!is_null($menu)){
                $path = $this->_getPath($currentPage, $menu);
            }
            $canShowBreadcrumbs = !!count($path);
            if (!is_null($homeText)){
                $homeUrl = call_user_func(array('\Context', 'getUrl'), 1, array(''));
                $homeItem = array(
                    'url'  => $homeUrl,
                    'text' => $homeText,
                    'href' => $homeUrl,
                    'node_srl' => 0,
                    'process_url'=>false
                );
                array_unshift($path, $homeItem);
            }
            $breadcrumbs = '';
            if ($canShowBreadcrumbs) {
                $breadcrumbs .= '<ul class="breadcrumb">';
                foreach ($path as $key=>$item){
                    $breadcrumbs .= '<li>';
                    if ($key < count($path) - 1){//if not last item
                        if ($item['process_url']){
                            $urlParams = array('mid', $item['url']);
                            $url = call_user_func(array('\Context', 'getUrl'), count($urlParams), $urlParams);
                        }
                        else{
                            $url = $item['url'];
                        }
                        $breadcrumbs .= '<a href="'.$url.'" title="'.$item['text'].'">'.$item['text'].'</a>';
                        $breadcrumbs .= $separator;
                    }
                    else{
                        $breadcrumbs .= '<span title="'.$item['text'].'">'.$item['text'].'</span>';
                    }
                    $breadcrumbs .= '</li>';
                }
                $breadcrumbs .= '</ul>';
            }
            $this->_breadcrumbs = $breadcrumbs;
        }
        return $this->_breadcrumbs;
    }

    /**
     * get the path in menu recursively
     * @param $identifier
     * @param $menu
     * @return array
     */
    protected function _getPath($identifier, $menu){
        $menu = (array)$menu;
        $path = array();
        if (isset($menu['list'])){
            foreach ($menu['list'] as $item){
                if (isset($item['url']) && $item['url'] == $identifier){
                    $path[] = $this->_itemToBreadcrumb($item);
                    break;
                }
                else{
                    $subPath = $this->_getPath($identifier, $item);
                    if (count($subPath) > 0){
                        $path[] = $this->_itemToBreadcrumb($item);
                        $path = array_merge($path, $subPath);
                    }
                }
            }
        }
        return $path;
    }

    /**
     * normalize all breadcrumb items
     * @param $item
     * @return array
     */
    protected function _itemToBreadcrumb($item){
        $breadcrumb = array();
        $attributes = array('url', 'text', 'href' , 'node_srl');
        foreach ($attributes as $attribute){
            if (isset($item[$attribute])){
                $breadcrumb[$attribute] = $item[$attribute];
            }
            else{
                $breadcrumb[$attribute] = '';
            }
        }
        $breadcrumb['process_url'] = true;
        return $breadcrumb;
    }
}