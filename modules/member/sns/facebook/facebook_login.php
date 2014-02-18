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
                
                $member->user_id=$profile['username'];
                $member->nick_name=$member->user_id;
                $member->user_name=$profile['name'];
                $member->email_address=$profile['email'];
                $member->profile_image=sprintf('http://graph.facebook.com/%s/picture?type=square', $profile['id']);
                $member->auth_type='facebook';
                $member->sns_postfix='fb';
                $member->sns_guid=$profile['id'];

                return $member;
            } catch (FacebookApiException $e) {
                return new Object(-1, $e->getMessage());
            }
        }elseif(isset ($_GET['code'])){
            return new Object(-1, 'unexpected_error');
        }else{
            $loginUrl = $facebook->getLoginUrl(array('scope' => 'email','redirect_uri' => getNotEncodedFullUrl('','act','procMemberSnsSignIn','sns','facebook')));
            header("Location: " . $loginUrl);
        }
        
    }
    
}

?>
