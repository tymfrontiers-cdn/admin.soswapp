<?php
// load up app settings
$conf_path = \str_replace([
  "/vendor/tymfrontiers-cdn/admin.soswapp",
  "/dev/tymfrontiers-cdn/admin.soswapp",
  "\\vendor\\tymfrontiers-cdn\\admin.soswapp",
  "\\dev\\tymfrontiers-cdn\\admin.soswapp",
],"",__DIR__);
$conf_file = $conf_path . "/.projectinfo";

if (!\file_exists($conf_file) || !\is_readable($conf_file)) {
  throw new \Exception("App config file missing/unreadable, kindly revert to 7 OS Web - app manual.", 1);
}

$conf = \trim(\file_get_contents($conf_file));
$conf = \json_decode($conf);
if (empty($conf->PRJ_ROOT)) {
  throw new \Exception("[PRJ_ROOT]: not set in app config.", 1);
}
$base_include = $conf->PRJ_ROOT;

if (!\file_exists($base_include)) {
  throw new \Exception("[\"{$base_include}\"]: does not exist, kindly revert to 7 OS Web - app manual.", 1);
}
\define('APP_BASE_INC', $base_include . "/.baseinit.php");
\define('APP_ROOT', __DIR__);
