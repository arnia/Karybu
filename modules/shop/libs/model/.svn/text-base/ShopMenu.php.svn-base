<?php
/**
 * File containing the ShopMenu class
 */
/**
 * Class representing a menu used in an XE shop
 *
 * Menus can be added from shop backend;
 * they use the XE Core menu mechanism
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class ShopMenu
{
    const MENU_TYPE_HEADER = 'header_menu',
            MENU_TYPE_FOOTER = 'footer_menu';

    private $_menu = NULL;

	/**
	 * Constructor
	 *
	 * Checks to see if a menu cache file exists, and
	 * if not it creates it <br />
	 * Loads the menu from the cache file and saves it
	 * in the $_menu private property
	 *
	 * @param $menu_srl
	 */
	public function __construct($menu_srl)
    {
        if(!isset($menu_srl))
        {
            return NULL;
        }
        /**
         * @var menuAdminModel $menuModel
         */
        $menuModel = getAdminModel('menu');
        $shop_menu = $menuModel->getMenu($menu_srl);
        if(!file_exists($shop_menu->php_file))
        {
            $menuAdminController = getAdminController('menu');
            $menuAdminController->makeXmlFile($menu_srl);
        }

        $menu = NULL;
        @include($shop_menu->php_file); // Populates $menu with menu data
        $this->_menu = $menu;
        return $menu;
    }

	/**
	 * Returns the HTML code for displaying a menu
	 *
	 * This is in order to hide the menu logic from
	 * the template files
	 *
	 * @return string
	 */
	public function getHtml()
    {
        $menu_html = '<ul>';
        if($this->_menu)
        {
            foreach($this->_menu->list as $key1 => $val1)
            {
                // Open LI
                $menu_html .= '<li';
                if($val1['selected'])
                {
                    $menu_html .= ' class="active ';
                }
                $menu_html .= '>';

                // Link
                $menu_html .= '<a href="' . $val1['href']  .'"';
                if($val1['open_window'] == 'Y')
                {
                    $menu_html .= ' target="_blank"';
                }
                $menu_html .= '>';

                // Link text
                $menu_html .= $val1['link'];
                $menu_html .= '</a>';

                // Second level menu
                if($val1['list'])
                {
                    $menu_html .= '<ul>';
                    foreach($val1['list'] as $key2 => $val2)
                    {
                        // Open LI
                        $menu_html .= '<li';
                        if($val2['selected'])
                        {
                            $menu_html .= ' class="active ';
                        }
                        $menu_html .= '>';

                        // Link
                        $menu_html .= '<a href="' . $val2['href']  .'"';
                        if($val2['open_window'] == 'Y')
                        {
                            $menu_html .= ' target="_blank"';
                        }
                        $menu_html .= '>';

                        // Link text
                        $menu_html .= $val1['link'];
                        $menu_html .= '</a>';

                        $menu_html .= '</li>';
                    }

                    $menu_html .= '</ul>';
                }

                $menu_html .= '</li>';
            }
        }
        $menu_html .= '</ul>';
        return $menu_html;
    }
}