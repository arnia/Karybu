<?php
function getPlatformCaption($platform){
    global $lang;
    return $lang->$platform;
}

function getPlatformKeyCaption($platform){
    global $lang;
    $prop = $platform.'_key';
    return $lang->$prop;
}
function getBooleanCaption($bool){
    global $lang;
    $prop='false';
    if($bool){
        $prop='true';
    }
    return $lang->$prop;
}

function getPlatformAddKeyCaption($platform){
    global $lang;
    $prop = $platform.'_add_key';
    return $lang->$prop;
}

function getPlatformBuildStatus($platform,$status){
    global $lang;
    $prop = $platform.'_'.$status;
    return $lang->$prop;
}

