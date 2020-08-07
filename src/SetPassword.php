<?php
namespace TymFrontiers;
use \SOS\Admin;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login();

$post = \json_decode( \file_get_contents('php://input'), true); // json data
$post = !empty($post) ? $post : (
  !empty($_POST) ? $_POST : []
);
$gen = new Generic;
$auth = new API\Authentication ($api_sign_patterns);
$http_auth = $auth->validApp ();
if( !$http_auth && ( empty($post['form']) || empty($post['CSRF_token']) ) ){
  HTTP\Header::unauthorized (false,'', Generic::authErrors ($auth,"Request [Auth-App]: Authetication failed.",'self',true));
}
$params = $gen->requestParam(
  [
    "password_old" =>["password_old","text",5,16],
    "password" =>["password","text", 8, 20],
    "password_repeat" =>["password_repeat","text", 8, 20],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ["password_old","password","password_repeat",'CSRF_token','form']
);
if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError($gen,true))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request failed"
  ]);
  exit;
}

if( !$http_auth ){
  if ( !$gen->checkCSRF($params["form"],$params["CSRF_token"]) ) {
    $errors = (new InstanceError($gen,true))->get("checkCSRF",true);
    echo \json_encode([
      "status" => "3." . \count($errors),
      "errors" => $errors,
      "message" => "Request failed."
    ]);
    exit;
  }
}

$user = Admin::authenticate($session->user->email,$params["password_old"]);
if( !$user ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Invalid old Password."],
    "message" => "Request failed"
  ]);
  exit;
}
if ( $params['password'] !== $params['password_repeat']) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["[password] and [password_repeat] does not match."],
    "message" => "Request failed"
  ]);
  exit;
}
$pwd = Data::pwdHash($params['password']);
// change password
$admin_db = MYSQL_ADMIN_DB;
if (!$database->query("UPDATE {$admin_db}.`user` SET password='{$database->escapeValue($pwd)}' WHERE email = '{$database->escapeValue($session->user->email)}' LIMIT 1")) {
  echo \json_encode([
    "status" => "4.1",
    "errors" => ["Failed to change password, please try again later."],
    "message" => "Request failed."
  ]);
  exit;
}

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!"
]);
exit;
