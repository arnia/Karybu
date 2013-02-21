<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 2/20/13
 * Time: 3:05 PM
 * To change this template use File | Settings | File Templates.
 */
namespace Flow;

interface FlowActionRegister
{
    /**
     * @param $ACTION_TYPE - one of the constants defined in Flow\ACTION_TYPES
     * @param $form - form name of whom leaving event the action will be called
     * @param $action - model function to be called
     * @param int $priority - a higher number assure the action will have a greater priority
     * @return mixed
     * @throw FlowException if something is wrong
     */
    public function registerAction($ACTION_TYPE, $form, callable $action, $priority=0);
}
