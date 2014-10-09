<?php
/**
 * PhoneGap build api
 */

class PhoneGapBuilder{
    private $chanel;
    private static $instance=null;

    const PHONEGAP_URL='https://build.phonegap.com';
    const PLATFORM_ANDROID='android',PLATFORM_IOS='ios',PLATFORM_WIN='winphone';
    const STATUS_COMPLETE='complete',STATUS_SKIP='skip',STATUS_ERROR='error';


    public static function getInstance($userName,$password){
        if(is_null(self::$instance)){
            self::$instance = new PhoneGapBuilder($userName,$password);
        }
        return self::$instance;
    }
    public static function getSupportedPlatforms(){
        return array('android','blackberry','ios','symbian','webos','winphone');
    }
    public static function getSupportedPlatformKeys(){
        return array('android','ios');
    }

    private function __construct($userName,$password){
        $this->chanel = curl_init();
        curl_setopt($this->chanel, CURLOPT_USERPWD, $userName.':'.$password);
        curl_setopt ($this->chanel, CURLOPT_RETURNTRANSFER,true) ;
        curl_setopt($this->chanel, CURLOPT_SSL_VERIFYPEER, false);
    }
    //-------------Application----------
    public function registerNewAppUsingFile($title,$fileName,$package='',$version='',$description='',$appKeys=array(),$private=true,$share=false){
        $data = new stdClass();
        $data->create_method='file';
        $data->title = $title;
        $data->private=$private;
        $data->share=$share;
        if(!empty($appKeys)){
            $data->keys = new stdClass();
            foreach($appKeys as $key =>$val){
                $data->keys->$key = $val;
            }
        }
        if(!empty($package)) $data->pacakge=$package;
        if(!empty($version)) $data->version=$version;
        if(!empty($description)) $data->description=$description;
        $params = array('data'=>json_encode($data),
                        'file'=>'@'.$fileName
                       );
        if(!empty($args)){
            array_push($params,$args);
        }
        return $this->_request(self::PHONEGAP_URL.'/api/v1/apps',$params);
    }
    public function getUserApps(){
        return $this->_request(self::PHONEGAP_URL.'/api/v1/apps');
    }

    public function updateApp($id,$title='',$fileName,$package='',$version='',$description='',$appKeys=array(),$private=true,$share=false){
        $url = self::PHONEGAP_URL.'/api/v1/apps/'.$id;
        $data = new stdClass();
        $params = array();

        if(!empty($title)){
            $data->title = $title;
        }
        if(!empty($package)){
            $data->package = $package;
        }
        if(!empty($version)){
            $data->version = $version;
        }
        if(!empty($description)){
            $data->description = $description;
        }
        if(!empty($private)){
            $data->private = $private;
        }
        if(!empty($share)){
            $data->share = $share;
        }
        if(!empty($appKeys)){
            $data->keys = new stdClass();
            foreach($appKeys as $key =>$val){
                $data->keys->$key = $val;
            }
        }
        $dataArray = get_object_vars($data);
        if(!empty($dataArray)){
            $params['data']=json_encode($data);
        }
        if(!empty($fileName)){
            $params['file']='@'.$fileName;
        }

        curl_setopt($this->chanel,CURLOPT_CUSTOMREQUEST,'PUT');
        return $this->_request($url,$params);

    }
    public function deleteApp($id){
        $url = self::PHONEGAP_URL.'/api/v1/apps/'.$id;
        curl_setopt($this->chanel,CURLOPT_CUSTOMREQUEST,'DELETE');
        return $this->_request($url);
    }
    public function getAppIcon($id){
        $url = self::PHONEGAP_URL.'/api/v1/apps/'.$id.'/icon';
        return $this->_request($url);
    }
    public function getAppByID($id){
        $url = self::PHONEGAP_URL.'/api/v1/apps/'.$id;
        return $this->_request($url);
    }
    //--------key------------
    public function getKeys($platform=''){
        $url = self::PHONEGAP_URL.'/api/v1/keys';
        if(!empty($platform)){
            $url=$url.'/'.$platform;
        }
        return $this->_request($url);
    }
    public function getKeyByID($id,$platform){
        $url=self::PHONEGAP_URL.'/api/v1/keys/'.$platform.'/'.$id;
        return $this->_request($url);
    }
    public function addAndroidKey($keystoreFile,$title,$alias,$keystorePassword='',$keyPassword=''){
        $data = new stdclass();
        $data->title=$title;
        $data->alias=$alias;
        if(!empty($keystorePassword))
            $data->keystore_pw=$keystorePassword;
        if(!empty($keyPassword))
            $data->key_pw=$keyPassword;
        $param = array('keystore'=>'@'.$keystoreFile,
                       'data'=>json_encode($data)
                      );
        $url = self::PHONEGAP_URL.'/api/v1/keys/'.self::PLATFORM_ANDROID;
        return $this->_request($url,$param);
    }
    public function updateAndroidKey($id,$title,$alias,$keystoreFile='',$keystorePassword='',$keyPassword=''){
        $data = new stdclass();
        $data->title=$title;
        $data->alias=$alias;
        if(!empty($keystorePassword))
            $data->keystore_pw=$keystorePassword;
        if(!empty($keyPassword))
            $data->key_pw=$keyPassword;

        $param = array(
            'data'=>json_encode($data)
        );
        if(!empty($keystoreFile))
            $param['keystore']='@'.$keystoreFile;
        $param = array(
            'data'=>json_encode($data)
        );
        $url = self::PHONEGAP_URL.'/api/v1/keys/'.self::PLATFORM_ANDROID.'/'.$id;
        curl_setopt($this->chanel,CURLOPT_CUSTOMREQUEST,'PUT');
        return $this->_request($url,$param);
    }
    public function unlockAndroidKey($id,$keystorePassword,$keyPassword){
        $url = self::PHONEGAP_URL.'/api/v1/keys/'.self::PLATFORM_ANDROID.'/'.$id;
        $data = new stdclass();
        $data->keystore_pw=$keystorePassword;
        $data->key_pw=$keyPassword;
        $param = array(
            'data'=>json_encode($data)
        );
        curl_setopt($this->chanel,CURLOPT_CUSTOMREQUEST,'PUT');
        return $this->_request($url,$param);
    }

    public function addIOSKey($certFile,$profileFile,$title,$password=''){
        $data = new stdClass();
        $data->title = $title;
        if(!empty($password))
            $data->password=$password;
        $param = array('cert'=>'@'.$certFile,
                       'profile'=>'@'.$profileFile,
                       'data'=>json_encode($data)
                      );
        $url = self::PHONEGAP_URL.'/api/v1/keys/'.self::PLATFORM_IOS;
        return $this->_request($url,$param);
    }
    public function updateIOSKey($id,$title,$certFile='',$profileFile='',$password=''){
        $data = new stdClass();
        $data->title = $title;
        if(!empty($password))
            $data->password=$password;
        $param = array(
            'data'=>json_encode($data)
        );
        if(!empty($certFile))
            $param['cert']='@'.$certFile;
        if(!empty($profileFile))
            $param['profile']='@'.$profileFile;

        $url = self::PHONEGAP_URL.'/api/v1/keys/'.self::PLATFORM_IOS.'/'.$id;
        curl_setopt($this->chanel,CURLOPT_CUSTOMREQUEST,'PUT');
        return $this->_request($url,$param);
    }

    public function unlockIOSKey($id,$password){
        $url = self::PHONEGAP_URL.'/api/v1/keys/'.self::PLATFORM_IOS.'/'.$id;
        $data = new stdclass();
        $data->password=$password;
        $param = array(
            'data'=>json_encode($data)
        );
        curl_setopt($this->chanel,CURLOPT_CUSTOMREQUEST,'PUT');
        return $this->_request($url,$param);
    }
    public function deleteKey($id,$platform){
        $url = self::PHONEGAP_URL.'/api/v1/keys/'.$platform.'/'.$id;
        curl_setopt($this->chanel,CURLOPT_CUSTOMREQUEST,'DELETE');
        return $this->_request($url);
    }
    //-----------app download----------------
    public function getAppDownload($id,$platform){
        $url = self::PHONEGAP_URL.'/api/v1/apps/'.$id.'/'.$platform;
        return $this->_request($url);
    }
    //-----------private functions-----------
    private function _request($url,$param=null){
        curl_setopt($this->chanel, CURLOPT_URL, $url);
        if(!empty($param)){
            curl_setopt($this->chanel,CURLOPT_POSTFIELDS,$param);
        }
        $result = curl_exec($this->chanel);
        return json_decode($result);
    }
    public function __destruct(){
        curl_close($this->chanel);
    }
}