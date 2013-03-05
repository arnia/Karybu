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

            $email=$profile->emails[0]->handle;
            
            $member->user_id=substr($email, 0, strpos($email, '@'));
            $member->user_name=$member->user_id;
            $member->user_name=$profile->givenName;
            $member->email_address=$email;
            $member->profile_image=$profile_image = $profile->image->imageUrl;
            $member->auth_type='yahoo';
            $member->sns_postfix='yh';
            $member->sns_guid=$profile->guid;

            return $member;
        }
        else{
            return new Object(-1, 'msg_sns_config_error');
        }
        
    }
    
}

?>
