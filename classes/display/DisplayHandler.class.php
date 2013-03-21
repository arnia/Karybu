<?php
    /**
    * @class DisplayHandler
    * @author NHN (developers@xpressengine.com)
    *  DisplayHandler is responsible for displaying the execution result. \n
    *  Depending on the request type, it can display either HTML or XML content.\n
    *  Xml content is simple xml presentation of variables in oModule while html content
    *   is the combination of the variables of oModue and template files/.
    **/

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DisplayHandler extends Handler {

        var $content_size = 0; // /< The size of displaying contents

        var $gz_enabled = false; // / <a flog variable whether to call contents after compressing by gzip
		var $handler = null;

        var $headers = array();

        function addHeader($key, $header, $replace = true)
        {
            $this->headers[] = array($key, $header, $replace);
        }

        function isGzipEnabled($gzhandler_enable)
        {
            return (defined('__OB_GZHANDLER_ENABLE__') && __OB_GZHANDLER_ENABLE__ == 1) &&
                strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')!==false &&
                function_exists('ob_gzhandler') &&
                extension_loaded('zlib') &&
                $gzhandler_enable;
        }

        function getHandler()
        {
            if(Context::get('xeVirtualRequestMethod')=='xml') {
                require_once("./classes/display/VirtualXMLDisplayHandler.php");
                $handler = new VirtualXMLDisplayHandler();
            }
            else if(Context::getRequestMethod() == 'XMLRPC') {
                require_once("./classes/display/XMLDisplayHandler.php");
                $handler = new XMLDisplayHandler();
                if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) $this->gz_enabled = false;
            }
            else if(Context::getRequestMethod() == 'JSON') {
                require_once("./classes/display/JSONDisplayHandler.php");
                $handler = new JSONDisplayHandler();
            }
            else {
                require_once("./classes/display/HTMLDisplayHandler.php");
                $handler = new HTMLDisplayHandler();
            }
            return $handler;
        }

        function getContent($oModule)
        {
            // Check if the gzip encoding supported
            $this->gz_enabled = $this->isGzipEnabled($oModule->gzhandler_enable);

            // Extract contents to display by the request method
            $handler = $this->getHandler();
            $output = $handler->toDoc($oModule);
            $this->modifyOutput($output, $handler);

            // Print content
            if($this->gz_enabled) $output = ob_gzhandler($output, 5);

            return $output;
        }

        function modifyOutput(&$output, $handler)
        {
            // call a trigger before display
            ModuleHandler::triggerCall('display', 'before', $output);
            // execute add-on
            $called_position = 'before_display_content';
            $oAddonController = &getController('addon');
            $addon_file = $oAddonController->getCacheFilePath(Mobile::isFromMobilePhone()?"mobile":"pc");
            @include($addon_file);

            if(method_exists($handler, "prepareToPrint")) $handler->prepareToPrint($output);
        }

        function getStatusCode($oModule)
        {
            $code = $oModule->getHttpStatusCode();
            $message = Context::get('http_status_message');
            if(!$code) $code = 200;

            return array($code, $message);
        }

        function prepareHeaders($oModule)
        {
            // header output
            if($this->gz_enabled) $this->addHeader("Content-Encoding", "gzip");

            $httpStatusCode = $oModule->getHttpStatusCode();

            if(!$httpStatusCode || $httpStatusCode == 200) {
                if(Context::getResponseMethod() == 'JSON') $this->_printJSONHeader();
                else if(Context::getResponseMethod() != 'HTML') $this->_printXMLHeader();
                else $this->_printHTMLHeader();
            }
        }

        function getHeaders($oModule)
        {
            $this->headers = array();
            $this->prepareHeaders($oModule);
            return $this->headers;
        }

        /**
         * print either html or xml content given oModule object
         * @remark addon execution and the trigger execution are included within this method, which might create inflexibility for the fine grained caching
         * @param ModuleObject $oModule the module object
		 * @return void
         **/
        function printContent(&$oModule) {
            $output = $this->getContent($oModule);
            $headers = $this->getHeaders($oModule);
        }

		/**
		 * print a HTTP HEADER for XML, which is encoded in UTF-8
		 * @return void
		 **/
		function _printXMLHeader() {
            $this->addHeader("Content-Type", "text/xml; charset=UTF-8");
            $this->addHeader("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
            $this->addHeader("Last-Modified", gmdate("D, d M Y H:i:s") . " GMT");
            $this->addHeader("Cache-Control", "no-store, no-cache, must-revalidate");
            $this->addHeader("Cache-Control", "post-check=0, pre-check=0", false);
            $this->addHeader("Pragma", "no-cache");
		}


		/**
		 * print a HTTP HEADER for HTML, which is encoded in UTF-8
		 * @return void
		 **/
		function _printHTMLHeader() {
            $this->addHeader("Content-Type", "text/html; charset=UTF-8");
            $this->addHeader("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
            $this->addHeader("Last-Modified", gmdate("D, d M Y H:i:s") . " GMT");
            $this->addHeader("Cache-Control", "no-store, no-cache, must-revalidate");
            $this->addHeader("Cache-Control", "post-check=0, pre-check=0", false);
            $this->addHeader("Pragma", "no-cache");
		}


		/**
		 * print a HTTP HEADER for JSON, which is encoded in UTF-8
		 * @return void
		 **/
		function _printJSONHeader() {
            $this->addHeader("Content-Type", "text/html; charset=UTF-8");
            $this->addHeader("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
            $this->addHeader("Last-Modified", gmdate("D, d M Y H:i:s") . " GMT");
            $this->addHeader("Cache-Control", "no-store, no-cache, must-revalidate");
            $this->addHeader("Cache-Control", "post-check=0, pre-check=0", false);
            $this->addHeader("Pragma", "no-cache");
		}

        /**
         * Helper function for generating a Response object from a module
         *
         * @param ModuleObject $oModule
         * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
         */
        function getReponseForModule(ModuleObject $oModule)
        {
            $response = new Response();

            // 1. Status code
            $status_code = $this->getStatusCode($oModule);
            $response->setStatusCode($status_code[0], $status_code[1]);

            // 2. Headers
            $headers = $this->getHeaders($oModule);
            foreach ($headers as $header) {
                $response->headers->set($header[0], $header[1], $header[2]);
            }

            // 3. Location header
            $lookingForLocation = headers_list();
            foreach ($lookingForLocation as $header) {
                $hSplit = explode(':', $header, 2);
                $hTarget = trim($hSplit[1]); $hName = trim($hSplit[0]);
                if (strtolower($hName) == 'location') {
                    header_remove('location');
                    $response = new RedirectResponse($hTarget);
                }
            }

            // 4. The content
            if (!($response instanceof RedirectResponse)) {
                $content = $this->getContent($oModule);
                $response->setContent($content);
            }

            return $response;
        }

    }
?>
