<?php
// florin, 6/7/13, 12:27 PM

namespace Karybu\Twig\Extension;

use Karybu\Twig\Extension\Tag\LoadTokenParser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Cms extends \Twig_Extension implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;
    protected $_breadcrumbs = null;

    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    public function getName()
    {
        return 'karybu';
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('loadJs', array($this, 'loadJs')),
            new \Twig_SimpleFunction('loadCss', array($this, 'loadCss')),
            new \Twig_SimpleFunction('breadcrumbs', array($this, 'getBreadcrumbs'))
        );
    }

    public function getGlobals()
    {
        return array(
            'context' => \Context::getInstance(),
            'request' => $this->container->get('request')
        );
    }

    public function getTokenParsers()
    {
        return array(
            new LoadTokenParser()
        );
    }


    /**
     * Functions
     */

    public function loadFile($args, $useCdn = false, $cdnPrefix = '', $cdnVersion = '')
    {
        \Context::loadFile($args, $useCdn, $cdnPrefix, $cdnVersion);
    }

    /**
     * \FrontendFileHandler::loadFile()
     * case js
     *        $args[0]: file name
     *        $args[1]: type (head | body)
     *        $args[2]: target IE
     *        $args[3]: index
     *
     * @param $path
     */
    public function loadJs($path, $type='head', $targetIE=null, $index=null)
    {
        if (substr($path, -3) != '.js') {
            throw new \Twig_Error('The js file should end in .js');
        }
        $this->loadFile(array($path, $type, $targetIE, $index));
    }

    /**
     * \FrontendFileHandler::loadFile()
     * case css
     *        $args[0]: file name
     *        $args[1]: media
     *        $args[2]: target IE
     *        $args[3]: index
     *
     * @param $path
     */
    public function loadCss($path, $media=null, $targetIE=null, $index=null)
    {
        if (substr($path, -4) != '.css') {
            throw new \Twig_Error('A the css file should end in .css');
        }
        $this->loadFile(array($path, $media, $targetIE, $index));
    }

    /**
     * get the html for breadcrumbs
     * @param null $currentPage
     * @param null $menu
     * @param null $homeText
     * @return array
     */
    public function getBreadcrumbs($currentPage = null, $menu = null, $homeText = null){
        //echo "<pre>"; print_r($menu);exit;
        if (is_null($this->_breadcrumbs)){
            if (is_null($currentPage)){
                $this->_breadcrumbs = array();
                return $this->_breadcrumbs;
            }
            $path = array();
            if (!is_null($homeText)){
                $homeUrl = call_user_func(array('\Context', 'getUrl'), 1, array(''));
                $homeItem = array(
                    'url'       => $homeUrl,
                    'text'      => $homeText,
                    'href'      => $homeUrl,
                    'node_srl'  => 0
                );
                $path[$homeItem['text']] = $homeItem;
            }
            if (!is_null($menu)){
                foreach ($this->_getPath($currentPage, $menu) as $item){
                    $path[$item['text']] = $item;
                }

            }
            //see if last node is a board page. If so then add the article in breadcrumbs
            $last = end($path);
            if ($last){
                $nodeSrl = $last['node_srl'];
                \Context::set('menu_item_srl', $nodeSrl);
                $model = &getAdminModel('menu');
                $model->getMenuAdminItemInfo();
                if (isset($model->variables['menu_item'])){
                    $menuItem = $model->variables['menu_item'];
                    if (isset($menuItem->moduleType) && $menuItem->moduleType == 'board') {
                        //add current document
                        $documentSrl = \Context::get('document_srl');
                        if ($documentSrl){
                            $documentModel = &getModel('document');
                            $document = $documentModel->getDocument($documentSrl);
                            $title = $document->getTitle();
                            if (!empty($title)){
                                $documentItem = array(
                                    'url'       => \getUrl('document_srl',$document->document_srl),
                                    'text'      => $title,
                                    'href'      => \getUrl('document_srl',$document->document_srl),
                                    'node_srl'  => $document->document_srl
                                );
                                $path[$documentItem['text']] = $documentItem;
                            }
                        }
                    }
                }
            }
            $this->_breadcrumbs = array_values($path);
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
        $attributes = array('url', 'text', 'href', 'node_srl');
        foreach ($attributes as $attribute){
            if (isset($item[$attribute])){
                $breadcrumb[$attribute] = $item[$attribute];
            }
            else{
                $breadcrumb[$attribute] = '';
            }
        }
        $urlParams = array('mid', $item['url']);
        $url = call_user_func(array('\Context', 'getUrl'), count($urlParams), $urlParams);
        $breadcrumb['url'] = $url;
        return $breadcrumb;
    }
}