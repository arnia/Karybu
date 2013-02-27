<?php

require_once './modules/member/sns/ISns.php';
require_once 'lib/Google_Client.php';
require_once 'lib/contrib/Google_Oauth2Service.php';

class google_login implements ISns{
    
    function isConfigValid($config) {
        return (
                !empty($config->client_id->value) 
                && !empty($config->client_secret->value)
                && !empty($config->redirect_uri->value)
                && !empty($config->developer_key->value)
        );
    }
    
    function doLogin($config){
        
        $client = new Google_Client();

        $client->setClientId($config->client_id->value);
        $client->setClientSecret($config->client_secret->value);
        $client->setRedirectUri($config->redirect_uri->value);
        $client->setDeveloperKey($config->developer_key->value);
        $oauth2 = new Google_Oauth2Service($client);

        if (isset($_GET['code'])) {
            $client->authenticate($_GET['code']);

            $profile = $oauth2->userinfo->get();

            //$member->user_id=$profile['id'];
            $member->nick_name=$profile['email'];
            $member->user_name=$profile['name'];
            $member->email_address=$profile['email'];
            $member->auth_type='google';
            $member->sns_guid=$profile['id'];

            return $member;
        } else {
            $authUrl = $client->createAuthUrl();
            header('location: '.$authUrl);
        }
        
    }
    
}

?>
