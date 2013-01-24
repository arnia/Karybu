<?php
/**
 * @file  index.php
 * @author NHN (developers@xpressengine.com)
 * @brief Start page
 *
 * Find and create module object by mif, act in Request Argument \n
 * Set module information
 *
 * @mainpage XpressEngine
 * @section intro introduction
 * XE is an opensource and being developed in the opensource project. \N
 * For more information, please see the link below.
 * - Official website: http://www.xpressengine.com(korean), http://www.xpressengine.org(english)
 * - SVN Repository: http://xe-core.googlecode.com/svn/
 * \n
 * "XpressEngine (XE)" is free software; you can redistribute it and/or \n
 * modify it under the terms of the GNU Lesser General Public \n
 * License as published by the Free Software Foundation; either \n
 * version 2.1 of the License, or (at your option) any later version. \n
 * \n
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * \n
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 **/

use Symfony\Component\HttpFoundation\Request;
use GlCMS\Core;

/**
 * Declare constants for generic use and for checking to avoid a direct call from the Web
 **/
define('__XE__',   true);
define('__ZBXE__', true); // deprecated : __ZBXE__ will be removed. Use __XE__ instead.

/**
 * Include the necessary configuration files
 **/
require dirname(__FILE__) . '/config/config.inc.php';

/** @var $container \Symfony\Component\DependencyInjection\Container */
$container = include __DIR__.'/src/container.php';
$request = Request::createFromGlobals();
/** @var $core \GlCMS\Core */
$core = $container->get('core');
$response = $core->handle($request);
$response->send();
$core->terminate($request, $response);