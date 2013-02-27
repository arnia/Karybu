<?php

require_once './modules/member/sns/ISns.php';
require_once 'lib/facebook.php';

class facebook_login implements ISns{
    
    function isConfigValid($config) {
        return (
                !empty($config->app_id->value) 
                && !empty($config->app_secret->value)
        );
    }
    
    function doLogin($config){
        
        $facebook = new Facebook(array('appId' => $config->app_id->value, 'secret' => $config->app_secret->value));

        $fbuser = $facebook->getUser();

        if ($fbuser) {
            try {
                $profile = $facebook->api('/me');
                
                //$member->user_id=$profile['id'];
                $member->nick_name=$profile['email'];
                $member->user_name=$profile['name'];
                $member->email_address=$profile['email'];
                $member->auth_type='facebook';
                $member->sns_guid=$profile['id'];

                return $member;
            } catch (FacebookApiException $e) {
                return new Object(-1, $e->getMessage());
            }
        }elseif(isset ($_GET['code'])){
            return new Object(-1, 'unexpected_error');
        }else{
            $loginUrl = $facebook->getLoginUrl(array('scope' => 'email'));
            header("Location: " . $loginUrl);
        }
        
    }
    
}

?>
