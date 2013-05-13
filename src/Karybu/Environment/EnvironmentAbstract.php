<?php
namespace Karybu\Environment;
abstract class EnvironmentAbstract
{
    /**
     * @var mixed - environment title
     */
    protected $_title = null;
    /**
     * get the code of the environment
     * @access public
     * @return string
     */
    abstract public function getCode();
    /**
     * get the language key of the environment $lang->key
     * @access public
     * @return string
     */
    abstract public function getLanguageKey();
    /**
     * check if developer mode is activated
     * @access public
     * @return string
     */
    abstract public function getDevMode();

    /**
     * set the environment title
     * @access public
     * @param string $title
     * @return EnvironmentAbstract
     */
    public function setTitle($title) {
        $this->_title = $title;
        return $this;
    }

    /**
     * get environment title
     * @access public
     * @return mixed
     */
    public function getTitle() {
        return $this->_title;
    }
}