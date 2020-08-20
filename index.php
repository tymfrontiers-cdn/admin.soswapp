<?php
namespace TymFrontiers;
require_once ".appinit.php";
require_once APP_BASE_INC;
require_once APP_ROOT . "/src/Helper.php";
HTTP\Header::redirect("/app/admin/dashboard");
