<?php

require_once './modules/member/sns/ISns.php';
require_once 'lib/Yahoo.inc';

class yahoo_login implements ISns{
    
    function isConfigValid($config) {
        return (
                !empty($config->oauth_consumer_key->value) 
                && !empty($config->oauth_consumer_secret->value)
                && !empty($config->oauth_app_id->value)
            );
    }
    
    function doLogin($config){
        
        $session = YahooSession::requireSession($config->oauth_consumer_key->value, $config->oauth_consumer_secret->value, $config->oauth_app_id->value);

        if (is_object($session)) {
            $yhuser = $session->getSessionedUser();
            $profile = $yhuser->getProfile();

            //$member->user_id=$profile->guid;
            $member->nick_name=$profile->emails[0]->handle;
            $member->user_name=$profile->givenName;
            $member->email_address=$profile->emails[0]->handle;
            $member->auth_type='yahoo';
            $member->sns_guid=$profile->guid;

            return $member;
        }
        else{
            return new Object(-1, 'msg_sns_config_error');
        }
        
    }
    
}

?>
