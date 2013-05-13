<?php
namespace Karybu\Environment;
class EnvironmentDevelopment extends EnvironmentAbstract
{
    /**
     * get the code of the environment
     * @return string
     */
    public function getCode() {
        return 'dev';
    }
    /**
     * get the language key of the environment $lang->key
     * @return string
     */
    public function getLanguageKey() {
        return 'dev';
    }
    /**
     * check if developer mode is activated
     * @return string
     */
    public function getDevMode() {
        return true;
    }
}