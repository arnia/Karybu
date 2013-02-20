<?php
/**
 * Use ModuleHandlerInstance instead,
 * so that we remove all static calls
 *
 * @deprecated
 */
class ModuleHandler
{
    /** @var null ModuleHandlerInstance */
    private static $module_handler = null;

    public static function setModuleHandler(ModuleHandlerInstance $module_handler)
    {
        self::$module_handler = $module_handler;
    }

    public static function getModuleInstance($module, $type = 'view', $kind = '')
    {
        $module_handler = new ModuleHandlerInstance();
        return $module_handler->getModuleInstance($module, $type, $kind);
    }

    public static function _getModuleFilePath($module, $type, $kind, &$classPath, &$highClassFile, &$classFile, &$instanceName)
    {
        $module_handler = new ModuleHandlerInstance();
        return $module_handler->_getModuleFilePath($module, $type, $kind, $classPath, $highClassFile, $classFile, $instanceName);
    }

    public static function getModulePath($module) {
        $module_handler = new ModuleHandlerInstance();
        return $module_handler->getModulePath($module);
    }

    public static function triggerCall($trigger_name, $called_position, &$obj) {
        $module_handler = new ModuleHandlerInstance();
        return $module_handler->triggerCall($trigger_name, $called_position, $obj);
    }
}