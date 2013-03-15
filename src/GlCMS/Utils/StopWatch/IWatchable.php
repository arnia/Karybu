<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 3/14/13
 * Time: 6:59 PM
 * To change this template use File | Settings | File Templates.
 */

namespace GlCMS\Utils\StopWatch;

use GlCMS\Utils\StopWatch\IStopWatch;


interface IWatchable {
    function setStopWatch(IStopWatch $stopWatch);
}