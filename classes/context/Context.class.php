<?php

class Context
{
    private static $context;

    public static function setRequestContext(NonStaticContext $context)
    {
        self::$context = $context;
    }

    public static function &getInstance()
    {
        return self::$context;
    }

    public static function get($key)
    {
        return self::$context->get($key);
    }

    public static function set($key, $value, $set_to_get_vars = 0)
    {
        self::$context->set($key, $value, $set_to_get_vars);
    }

    public static function gets()
    {
        $args_list = func_get_args();
        return call_user_func_array(array(self::$context, 'gets'), $args_list);
    }

    public static function getAll()
    {
        return self::$context->getAll();
    }

    public static function addBodyClass($class_name)
    {
        self::$context->addBodyClass($class_name);
    }

    public static function getBodyClass()
    {
        return self::$context->getBodyClass();
    }

    public static function getRequestMethod()
    {
        return self::$context->getRequestMethod();
    }
}