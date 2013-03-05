<?php

require_once './modules/member/sns/ISns.php';
require_once 'lib/Google_Client.php';
require_once 'lib/contrib/Google_Oauth2Service.php';

class google_login implements ISns{
    
    function isConfigValid($config) {
        return (
                !empty($config->client_id->value) 
                && !empty($config->client_secret->value)
                && !empty($config->callback_url->value)
                && !empty($config->developer_key->value)
        );
    }
    
    function doLogin($config){
        
        $client = new Google_Client();

        $client->setClientId($config->client_id->value);
        $client->setClientSecret($config->client_secret->value);
        $client->setRedirectUri($config->callback_url->value);
        $client->setDeveloperKey($config->developer_key->value);
        $oauth2 = new Google_Oauth2Service($client);

        if (isset($_GET['code'])) {
            $client->authenticate($_GET['code']);

            $profile = $oauth2->userinfo->get();
            
            $email=$profile['email'];

            $member->user_id=substr($email, 0, strpos($email, '@'));
            $member->nick_name=$member->user_id;
            $member->user_name=$profile['name'];
            $member->email_address=$email;
            $member->profile_image=$profile['picture'];
            $member->auth_type='google';
            $member->sns_postfix='gm';
            $member->sns_guid=$profile['id'];

            return $member;
        } else {
            $authUrl = $client->createAuthUrl();
            header('location: '.$authUrl);
        }
        
    }
    
}

?>
