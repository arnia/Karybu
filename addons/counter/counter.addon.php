<?php
if(!defined('__KARYBU__')) exit();

/**
 * @file counter.addon.php
 * @author Arnia (dev@karybu.org)
 * @brief Counter add-on
 **/
// Execute if called_position is before_display_content
if(Context::isInstalled() && $called_position == 'before_module_init' && Context::get('module')!='admin' && Context::getResponseMethod() == 'HTML') {
	$oCounterController = &getController('counter');
	$oCounterController->counterExecute();
}
?>
