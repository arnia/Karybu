<?php
namespace Karybu\Security;
class Csrf{
    const FORM_KEY_NAME     = 'form_key';
    const CHARS_LOWERS      = 'abcdefghijklmnopqrstuvwxyz';
    const CHARS_UPPERS      = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CHARS_DIGITS      = '0123456789';
    const FORM_KEY_LENGTH   = 10;
    /**
     * get the form input name
     * @return string
     */
    public function getFormKeyName(){
        return self::FORM_KEY_NAME;
    }
    /**
     * get the form session key
     * @return string
     */
    public function getSessionFormKey(){
        $keyName = $this->getFormKeyName();
        if (!isset($_SESSION[$keyName])){
            $chars = self::CHARS_LOWERS . self::CHARS_UPPERS . self::CHARS_DIGITS;
            mt_srand(10000000*(double)microtime());
            for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < self::FORM_KEY_LENGTH; $i++) {
                $str .= $chars[mt_rand(0, $lc)];
            }
            $_SESSION[$keyName] = $str;
        }
        return $_SESSION[$keyName];
    }
    /**
     * validate the form key
     * @return string
     */
    public function validateSessionFormKey($request){
        $method = $request->getMethod();
        if ($method == "POST"){
            $keyName = $this->getFormKeyName();
            $sentKey = \Context::get($keyName);
            if ($sentKey != $this->getSessionFormKey()){
                return false;
            }
        }
        return true;
    }
    //TODO: change the message and exception type
    /**
     * handle form key error
     * @throws Exception
     */
    public function formKeyError(){
        throw new CsrfException();
    }
}
