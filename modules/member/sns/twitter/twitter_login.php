<?php

    require_once './modules/member/sns/ISns.php';
    require_once 'lib/twitteroauth.php';
    
    class twitter_login implements ISns{
        
        function isConfigValid($config) {
            return (
                !empty($config->consumer_key->value) 
                && !empty($config->consumer_secret->value)
                && !empty($config->callback_url->value)
            );
        }
        
        function doLogin($config){
            if (!empty($_GET['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])) {
                $twitteroauth = new TwitterOAuth($config->consumer_key->value, $config->consumer_secret->value, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
                $access_token = $twitteroauth->getAccessToken($_GET['oauth_verifier']);
                $_SESSION['access_token'] = $access_token;
                $profile = $twitteroauth->get('account/verify_credentials');

                $member->user_id=$profile->screen_name;
                $member->user_name=$member->user_id;
                $member->user_name=$profile->name;
                $member->email_address='';
                $member->profile_image=$profile->profile_image_url;
                $member->auth_type='twitter';
                $member->sns_postfix='tw';
                $member->sns_guid=$profile->id;
                
                return $member;
            }
            
            $twitteroauth = new TwitterOAuth($config->consumer_key->value, $config->consumer_secret->value);
            $request_token = $twitteroauth->getRequestToken($config->callback_url->value);

            // Saving them into the session
            $_SESSION['oauth_token'] = $request_token['oauth_token'];
            $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
            if ($twitteroauth->http_code == 200) {
                $authUrl = $twitteroauth->getAuthorizeURL($request_token['oauth_token']);
                header('location: ' . $authUrl);
            } else {
                return new Object(-1, 'msg_sns_config_error');
            }
            
        }
        
    }

?>
