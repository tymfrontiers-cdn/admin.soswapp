<?php
// load up app settings
$prj_root = \preg_replace("/(\\\\|\/)(vendor|dev)(\\\\|\/)([a-z0-9\-\_\.]+)(\\\\|\/)([a-z0-9\-\_\.]+)/i", "", __DIR__);
\define('APP_BASE_INC', $prj_root . "/.baseinit.php");
\define('APP_ROOT', __DIR__);
