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
            new \Twig_SimpleFunction('context', array($this, 'cmsContext'))
        );
    }

    public function getGlobals()
    {
        return array(
            'k.context' => \Context::getInstance(),
            'k.request' => $this->container->get('request')
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
     *      * case js
     *        $args[0]: file name
     *        $args[1]: type (head | body)
     *        $args[2]: target IE
     *        $args[3]: index
     *
     * @param $path
     */
    public function loadJs($path, $type='head', $index=null)
    {
        if (substr($path, -3) != '.js') {
            throw new \Twig_Error('The js file should end in .js');
        }
        $this->loadFile(array($path, $type, null, $index));
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
    public function loadCss($path, $media=null, $index=null)
    {
        if (substr($path, -4) != '.css') {
            throw new \Twig_Error('A the css file should end in .css');
        }
        $this->loadFile(array($path, $media, null, $index));
    }

    public function cmsContext()
    {
        $args = func_get_args();
        if (!isset($args[0])) {
            throw new \Twig_Error('You did not mention the method to be called');
        }
        if (!method_exists('\Context', $method = array_shift($args))) {
            throw new \Twig_Error('No such method');
        }
        return call_user_func_array(array('\Context', $method), $args);
    }

}