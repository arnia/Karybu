<?php
namespace Karybu\Environment;
class EnvironmentProduction extends EnvironmentAbstract
{
    /**
     * get the code of the environment
     * @return string
     */
    public function getCode() {
        return 'prod';
    }
    /**
     * get the language key of the environment $lang->key
     * @return string
     */
    public function getLanguageKey() {
        return 'prod';
    }
    /**
     * check if developer mode is activated
     * @return string
     */
    public function getDevMode() {
        return false;
    }
}