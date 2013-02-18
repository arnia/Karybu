<?php

    if(!defined('__XE__')) require dirname(__FILE__).'/../../Bootstrap.php';

    require_once _XE_PATH_.'classes/context/Context.class.php';
    require_once _XE_PATH_.'classes/context/NonStaticContext.class.php';
    require_once _XE_PATH_.'classes/handler/Handler.class.php';
    require_once _XE_PATH_.'classes/xml/XmlParser.class.php';

    class FrontendFileHandler {}
    class FileHandler {}
    class Validator {}

class ContextTest extends PHPUnit_Framework_TestCase
{
    /**
     * test whether the singleton works
     */
    public function testGetInstance()
    {
        $file_handler = $this->getMock('FileHandler', array('getRealPath'));
        $context = new NonStaticContext($file_handler);
        Context::setRequestContext($context);

        $this->assertInstanceOf('NonStaticContext', Context::getInstance());
        $this->assertSame(Context::getInstance(), Context::getInstance());
    }

    public function testSetGetVars()
    {
        $context = new NonStaticContext();
        Context::setRequestContext($context);

        $this->assertSame(null, Context::get('var1'));
        Context::set('var1', 'val1');
        $this->assertSame('val1', Context::get('var1'));

        Context::set('var2', 'val2');
        $this->assertSame('val2', Context::get('var2'));
        Context::set('var3', 'val3');
        $data = new stdClass;
        $data->var1 = 'val1';
        $data->var2 = 'val2';
        $this->assertEquals($data, Context::gets('var1','var2'));
        $data->var3 = 'val3';
        $this->assertEquals($data, Context::getAll());
    }

    public function testAddGetBodyClass()
    {
        $context = new NonStaticContext();
        Context::setRequestContext($context);

        $this->assertEquals(Context::getBodyClass(), '');
        Context::addBodyClass('red');
        $this->assertEquals(Context::getBodyClass(), ' class="red"');
        Context::addBodyClass('green');
        $this->assertEquals(Context::getBodyClass(), ' class="red green"');
        Context::addBodyClass('blue');
        $this->assertEquals(Context::getBodyClass(), ' class="red green blue"');

        // remove duplicated class
        Context::addBodyClass('red');
        $this->assertEquals(Context::getBodyClass(), ' class="red green blue"');
    }
}