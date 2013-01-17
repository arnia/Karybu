<?php

/**
 * Groups the 3 required attributes to uniquely identify a module class
 *  module [like page/shop/board]
 *  type [view/controller/model]
 *  kind [admin]
 */
class ModuleKey
{
    private $module;
    private $type;
    private $kind;

    public function __construct($module, $type, $kind)
    {
        $this->module = $module;
        $this->type = $type;
        $this->kind = $kind;
    }

    public function isAdmin()
    {
        return $this->kind == 'admin';
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function getType()
    {
        return $this->type;
    }


}