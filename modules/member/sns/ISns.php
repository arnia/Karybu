<?php

/*
 * Required method for sns login
 * 
 */

interface ISns {
    
    /**
     * @brief validate the SNS configueration
     */
    function isConfigValid($config);

    /**
     * @brief process login
     */
    function doLogin($config);
    
}

?>
