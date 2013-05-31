<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 3/28/13
 * Time: 11:24 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Karybu\EventListener;

use Symfony\Component\HttpKernel\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Symfony\Component\HttpKernel\Exception\FlattenException;


class ExceptionHandler extends SymfonyExceptionHandler {

    private $debug;
    private $charset;

    public function __construct($debug = true, $charset = 'UTF-8')
    {
        $this->debug = $debug;
        $this->charset = $charset;
        parent::__construct($debug, $charset);
    }

    /**
     * Gets the HTML content associated with the given exception.
     *
     * @param FlattenException $exception A FlattenException instance
     *
     * @return string The content as a string
     */
    public function getContent(FlattenException $exception)
    {
        if($this->debug) {
            return $this->_getContentWhenDebugEnabled($exception);
        } else {
            return $this->_getContentWhenDebugDisabled($exception);
        }
    }

    private function _getContentWhenDebugEnabled(FlattenException $exception)
    {
        try {
            $content = '';
            $count = count($exception->getAllPrevious());
            $total = $count + 1;
            foreach ($exception->toArray() as $position => $e) {
                $ind = $count - $position + 1;
                $class = $this->abbrClass($e['class']);
                $message = nl2br($e['message']);
                $content .= sprintf(<<<EOF
                        <h2><span>%d/%d</span> <strong>%s</strong>: %s</h2>
                        <div class="block">
                            <ol class="traces list_exception">

EOF
                    , $ind, $total, $class, $message);
                foreach ($e['trace'] as $trace) {
                    $content .= '       <li>';
                    if ($trace['function']) {
                        $content .= sprintf('at %s%s%s(%s)', $this->abbrClass($trace['class']), $trace['type'], $trace['function'], $this->formatArgs($trace['args']));
                    }
                    if (isset($trace['file']) && isset($trace['line'])) {
                        if ($linkFormat = ini_get('xdebug.file_link_format')) {
                            $link = str_replace(array('%f', '%l'), array($trace['file'], $trace['line']), $linkFormat);
                            $content .= sprintf(' in <a href="%s" title="Go to source">%s line %s</a>', $link, $trace['file'], $trace['line']);
                        } else {
                            $content .= sprintf(' in %s line %s', $trace['file'], $trace['line']);
                        }
                    }
                    $content .= "</li>\n";
                }

                $content .= "    </ol>\n</div>\n";
            }
        } catch (\Exception $e) {
            // something nasty happened and we cannot throw an exception anymore
            $content = sprintf('Exception thrown when handling an exception (%s: %s)', get_class($exception), $exception->getMessage());
        }

        return <<<EOF
            <div id="k-resetcontent" class="k-reset">
                $content
            </div>
EOF;

    }

    private function _getContentWhenDebugDisabled(FlattenException $exception)
    {
        switch ($exception->getStatusCode()) {
            case 404:
                $title = 'Sorry, the page you are looking for could not be found.';
                break;
            default:
                $title = 'Whoops, looks like something went wrong.';
        }

        return <<<EOF

            <div id="k-resetcontent" class="k-reset">
                <h2><span>!</span>$title</h2>
            </div>
EOF;

    }

    /**
     * Copied from Symfony since there it is private
     * @param $class
     * @return string
     */
    private function abbrClass($class)
    {
        $parts = explode('\\', $class);

        return sprintf("<abbr title=\"%s\">%s</abbr>", $class, array_pop($parts));
    }

    /**
     * Formats an array as a string.
     * Copied from Symfony since there it is private
     *
     * @param array $args The argument array
     *
     * @return string
     */
    private function formatArgs(array $args)
    {
        $result = array();
        foreach ($args as $key => $item) {
            if ('object' === $item[0]) {
                $formattedValue = sprintf("<em>object</em>(%s)", $this->abbrClass($item[1]));
            } elseif ('array' === $item[0]) {
                $formattedValue = sprintf("<em>array</em>(%s)", is_array($item[1]) ? $this->formatArgs($item[1]) : $item[1]);
            } elseif ('string'  === $item[0]) {
                $formattedValue = sprintf("'%s'", htmlspecialchars($item[1], ENT_QUOTES | ENT_SUBSTITUTE, $this->charset));
            } elseif ('null' === $item[0]) {
                $formattedValue = '<em>null</em>';
            } elseif ('boolean' === $item[0]) {
                $formattedValue = '<em>'.strtolower(var_export($item[1], true)).'</em>';
            } elseif ('resource' === $item[0]) {
                $formattedValue = '<em>resource</em>';
            } else {
                $formattedValue = str_replace("\n", '', var_export(htmlspecialchars((string) $item[1], ENT_QUOTES | ENT_SUBSTITUTE, $this->charset), true));
            }

            $result[] = is_int($key) ? $formattedValue : sprintf("'%s' => %s", $key, $formattedValue);
        }

        return implode(', ', $result);
    }



    /**
     * Gets the stylesheet associated with the given exception.
     *
     * @param FlattenException $exception A FlattenException instance
     *
     * @return string The stylesheet as a string
     */
    public function getStylesheet(FlattenException $exception)
    {
        return <<<EOF
            body, * { font-family: Arial, "Helvetica Neue", Helvetica, sans-serif!important }
            body, html { background:#f8f8f8; }
            .k-reset { font-size: 11px; color: #333; box-shadow: 0px 3px 10px -10px rgba(0,0,0,0.3) }
            .k-reset .clear { clear:both; height:0; font-size:0; line-height:0; }
            .k-reset .clear_fix:after { display:block; height:0; clear:both; visibility:hidden; }
            .k-reset .clear_fix { display:inline-block; }
            .k-reset * html .clear_fix { height:1%; }
            .k-reset .clear_fix { display:block; }
            .k-reset, .k-reset .block { margin: auto }
            .k-reset abbr { border-bottom: none; cursor: help; }
            .k-reset p { font-size:14px; line-height:20px; color:#868686; padding-bottom:20px }
            .k-reset strong { font-weight:bold; color: #990000; }
            .k-reset a { color:#6c6159; }
            .k-reset a img { border:none; }
            .k-reset a:hover { text-decoration:underline; }
            .k-reset em { font-style:italic; }
            .k-reset h2 { font-size: 19px; line-height:24px; margin:55px 0 10px; background: #fff; border: 1px solid #ccc; border-radius:8px; padding: 6px 6px 6px 50px; margin-left: -50px; }
            .k-reset h2 strong { font-size:24px }
            .k-reset h2 span {
                width: 60px;
                height: 60px;
                font-weight: bold;
                color: #555555;
                border: 10px solid #bebebe;
                text-align: center;
                background: #ffffff;
                -webkit-box-shadow: 0px 1px 0px 0px #959595, inset 0px 1px 0px 0px #959595;
                box-shadow: 0px 1px 0px 0px #959595, inset 0px 1px 0px 0px #959595;
                -moz-border-radius: 160px;
                -webkit-border-radius: 160px;
                border-radius: 160px;
                margin-right:36px;
                margin-left:-105px;
                color: #333; padding: 6px;
                display:block;
                clear:both;
                position:absolute;
                font-size: 29px;
                vertical-align: middle;
                line-height:60px;
                margin-top:-32px
                }
            .k-reset .block { max-height:175px; overflow: auto; background: #fff; border-radius: 8px; padding: 10px; margin:0 10px 50px 0; border:1px solid #ccc; box-shadow: inset 0px 1px 3px 0px rgba(0,0,0,0.1); }
            .k-reset .traces { margin:0; padding:0 }
            .k-reset .traces li { font-size:12px; list-style-type:decimal; margin-left:20px; margin-bottom:3px; padding-bottom:3px; word-break:break-all; color: #777; font-family: "Courier New", Courier, monospace!important; }
            .k-reset .traces li em { color:#990000; font-family: "Courier New", Courier, monospace!important; }
            .k-reset .traces li abbr { font-family: "Courier New", Courier, monospace!important; color: #000; color:#000099 }

            .k-reset .block_exception {
                color:#555555;
                line-height:20px;
            }
            .k-reset li a { background:none; color:#868686; text-decoration:none; }
            .k-reset li a:hover { background:none; color:#313131; text-decoration:underline; }
            .k-reset ol { padding: 10px 0; line-height:20px }
            .k-reset h1 {
                margin: 25px 0 0;
                padding: 0;
                font-size: 26px;
                font-weight: bold;
                color:#878787;
            }
            #k-resetcontent { width:auto; margin:0 20px 0 110px; text-shadow: 0 1px 0 #ffffff }
EOF;
    }

    private function decorate($content, $css)
    {
        return <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="robots" content="noindex,nofollow" />
        <style>
            html{color:#000;background:#FFF;}body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,textarea,p,blockquote,th,td{margin:0;padding:0;}table{border-collapse:collapse;border-spacing:0;}fieldset,img{border:0;}address,caption,cite,code,dfn,em,strong,th,var{font-style:normal;font-weight:normal;}li{list-style:none;}caption,th{text-align:left;}h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:normal;}q:before,q:after{content:'';}abbr,acronym{border:0;font-variant:normal;}sup{vertical-align:text-top;}sub{vertical-align:text-bottom;}input,textarea,select{font-family:inherit;font-size:inherit;font-weight:inherit;}input,textarea,select{*font-size:100%;}legend{color:#000;}

            html { background: #eee; padding: 10px }
            img { border: 0; }
            #k-resetcontent { width:90%; margin:0 auto; }
            $css
        </style>
    </head>
    <body>
        $content
    </body>
</html>
EOF;
    }

}

