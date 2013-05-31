<?php
namespace Karybu\Security;
class Csrf{
    const FORM_KEY_NAME = 'form_key';

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
    //TODO: change the algorightm for key generation.
    public function getSessionFormKey(){
        $keyName = $this->getFormKeyName();
        if (!isset($_SESSION[$keyName])){
            $_SESSION[$keyName] = uniqid();
        }
        return $_SESSION[$keyName];
    }
    /**
     * validate the form key
     * @return string
     */
    //TODO: remove 'return true' enveryhing is in place (exec_json, exec_xml)
    public function validateSessionFormKey($request){
        return true;
        $method = $request->getMethod();
        if ($method == "POST"){
            $keyName = $this->getFormKeyName();
            $sentKey = $request->request->get($keyName, null);
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
        throw new Exception('Invalid Form Key. Try again');
    }
}
