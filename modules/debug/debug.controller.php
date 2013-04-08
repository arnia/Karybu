<?php
// florin, 4/8/13, 1:11 PM

class debugController extends debug
{

    function procDebugSaveToolbarSettings()
    {
        if (is_numeric($height = Context::get('height')) && $height > 0) {
            $_SESSION['debug_height'] = $height;
        }
        if ($state = Context::get('state')) {
            $_SESSION['debug_state'] = $state;
        }
        if ($tab = Context::get('tab')) {
            $_SESSION['debug_tab'] = $tab;
        }
    }

}