<?php

/**
 * Mobile class
 *
 * @author Arnia (dev@karybu.org)
 */
class MobileInstance
{
    /**
     * Whether mobile or not mobile mode
     * @var bool
     */
    var $ismobile = null;

    /**
     * Get current mobile mode
     *
     * @return bool If mobile mode returns true or false
     */
    function isFromMobilePhone()
    {
        if ($this->ismobile !== null) {
            return $this->ismobile;
        }

        $db_info = Context::getDBInfo();
        if (isset($db_info->use_mobile_view)) {
            if ($db_info->use_mobile_view != "Y" || Context::get('full_browse') || $_COOKIE["FullBrowse"]) {
                return ($this->ismobile = false);
            }
        }

        $xe_web_path = Context::pathToUrl(_KARYBU_PATH_);

        // default setting. if there is cookie for a device, Karybu do not have to check if it is mobile or not and it will enhance performace of the server.
        $this->ismobile = false;

        $m = Context::get('m');
        if (strlen($m) == 1) {
            if ($m == "1") {
                $this->ismobile = true;
            } elseif ($m == "0") {
                $this->ismobile = false;
            }
        } elseif (isset($_COOKIE['mobile'])) {
            if ($_COOKIE['user-agent'] == md5($_SERVER['HTTP_USER_AGENT'])) {
                if ($_COOKIE['mobile'] == 'true') {
                    $this->ismobile = true;
                } else {
                    $this->ismobile = false;
                }
            } else {
                $this->ismobile = false;
                setcookie("mobile", false, 0, $xe_web_path);
                setcookie("user-agent", false, 0, $xe_web_path);
                if (!$this->isMobilePadCheckByAgent() && $this->isMobileCheckByAgent()) {
                    $this->ismobile = true;
                }
            }
        } else {
            if ($this->isMobilePadCheckByAgent()) {
                $this->ismobile = false;
            } else {
                if ($this->isMobileCheckByAgent()) {
                    $this->ismobile = true;
                }
            }
        }

        if ($this->ismobile !== null) {
            if ($this->ismobile == true) {
                if ($_COOKIE['mobile'] != 'true') {
                    $_COOKIE['mobile'] = 'true';
                    setcookie("mobile", 'true', 0, $xe_web_path);
                }
            } elseif (!isset($_COOKIE['mobile']) || $_COOKIE['mobile'] != 'false') {
                $_COOKIE['mobile'] = 'false';
                setcookie("mobile", 'false', 0, $xe_web_path);
            }

            if (!isset($_COOKIE['user-agent']) || $_COOKIE['user-agent'] != md5($_SERVER['HTTP_USER_AGENT'])) {
                setcookie("user-agent", md5($_SERVER['HTTP_USER_AGENT']), 0, $xe_web_path);
            }
        }

        return $this->ismobile;
    }

    /**
     * Detect mobile device by user agent
     *
     * @return bool Returns true on mobile device or false.
     */
    function isMobileCheckByAgent()
    {
        static $UACheck;
        if (isset($UACheck)) {
            return $UACheck;
        }

        // stripos is only for PHP5.
        $mobileAgent = unserialize(
            strtolower(
                serialize(
                    array(
                        'iPod',
                        'iPhone',
                        'Android',
                        'BlackBerry',
                        'SymbianOS',
                        'Bada',
                        'Kindle',
                        'Wii',
                        'SCH-',
                        'SPH-',
                        'CANU-',
                        'Windows Phone',
                        'Windows CE',
                        'POLARIS',
                        'Palm',
                        'Dorothy Browser',
                        'Mobile',
                        'Opera Mobi',
                        'Opera Mini',
                        'Minimo',
                        'AvantGo',
                        'NetFront',
                        'Nokia',
                        'LGPlayer',
                        'SonyEricsson',
                        'HTC'
                    )
                )
            )
        );

        if ($this->isMobilePadCheckByAgent()) {
            $UACheck = true;
            return true;
        }

        foreach ($mobileAgent as $agent) {
            // stripos is only for PHP5..
            $httpUA = strtolower($_SERVER['HTTP_USER_AGENT']);
            if (strpos($httpUA, $agent) !== false) {
                $UACheck = true;
                return true;
            }
        }
        $UACheck = false;
        return false;
    }

    /**
     * Check if user-agent is a tablet PC as iPad or Andoid tablet.
     *
     * @return bool TRUE for tablet, and FALSE for else.
     */
    function isMobilePadCheckByAgent()
    {
        static $UACheck;
        if (isset($UACheck)) {
            return $UACheck;
        }
        $padAgent = array('iPad', 'Android', 'webOS', 'hp-tablet', 'PlayBook');

        // Android with 'Mobile' string is not a tablet-like device, and 'Andoroid' without 'Mobile' string is a tablet-like device.
        // $exceptionAgent[0] contains exception agents for all exceptions.
        $exceptionAgent = array(0 => array('Opera Mini', 'Opera Mobi'), 'Android' => 'Mobile');

        foreach ($padAgent as $agent) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], $agent) !== false) {
                if (!isset($exceptionAgent[$agent])) {
                    $UACheck = true;
                    return true;
                } elseif (strpos($_SERVER['HTTP_USER_AGENT'], $exceptionAgent[$agent]) === false) {
                    // If the agent is the Android, that can be either tablet and mobile phone.
                    foreach ($exceptionAgent[0] as $val) {
                        if (strpos($_SERVER['HTTP_USER_AGENT'], $val) !== false) {
                            $UACheck = false;
                            return false;
                        }
                    }
                    $UACheck = true;
                    return true;
                }
            }
        }

        $UACheck = false;
        return false;
    }

    /**
     * Set mobile mode
     *
     * @param bool $ismobile
     * @return void
     */
    function setMobile($ismobile)
    {
        $this->ismobile = $ismobile;
    }
}

/**
 * Will be replaced with MobileInstance
 *
 * @deprecated
 */
class Mobile
{
    /** @var MobileInstance */
    private static $mobile;

    public static function setRequestMobileInfo(MobileInstance $mobile)
    {
        self::$mobile = $mobile;
    }

    public static function &getInstance()
    {
        return self::$mobile;
    }

    public static function isFromMobilePhone()
    {
        return self::$mobile->isFromMobilePhone();
    }

    public static function isMobileCheckByAgent()
    {
        return self::$mobile->isMobileCheckByAgent();
    }

    public static function isMobilePadCheckByAgent()
    {
        return self::$mobile->isMobilePadCheckByAgent();
    }

    public static function setMobile($mobile)
    {
        self::$mobile->setMobile($mobile);
    }

}