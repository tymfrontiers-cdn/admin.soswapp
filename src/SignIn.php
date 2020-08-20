<?php
namespace TymFrontiers;
use \SOS\Admin;
require_once "../.appinit.php";
require_once APP_BASE_INC;

$data = new Data;
\header("Content-Type: application/json");
$gen = new Generic();

$params = $gen->requestParam(
  [
    "email" =>["email","email"],
    "password" =>["password","text",6,16],
    "remember" => ["remember","boolean"],

    "rdt" => ["rdt","url"],
    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  "post",
  ["email","password",'CSRF_token','form']
);
if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError($gen,true))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request failed",
    "rdt" => ""
  ]);
  exit;
}

if( !$gen->checkCSRF($params["form"],$params["CSRF_token"]) ){
  $errors = (new InstanceError($gen,true))->get("checkCSRF",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request failed.",
    "rdt" => ""
  ]);
  exit;
}

$user = Admin::authenticate($params["email"],$params["password"]);
if( !$user ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Credentials validation failed."],
    "message" => "Login failed",
    "rdt" => ""
  ]);
  exit;
}
$remember = !(bool)$params['remember'] ? \strtotime("+ 1 Hour") : \strtotime("+ 12 Hours");
// echo " <tt> <pre>";
// echo "</pre></tt>";
// \print_r($user);
// exit;
$session->login($user,$remember);
$database = new MySQLDatabase(MYSQL_SERVER, MYSQL_USER_USERNAME, MYSQL_USER_PASS);
$rdt = empty($params['rdt'])
  ? WHOST . "/work-domain"
  : $params['rdt'];

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Your are now signed in!",
  "rdt" => $rdt
]);
exit;
