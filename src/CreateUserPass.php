<?php
namespace TymFrontiers;
use \SOS\Admin;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");

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
    "email" =>["email","email"],
    "token" =>["token","text",3,128],
    "password" =>["password","password", 8, 20],
    "password_repeat" =>["password_repeat","password", 8, 20],

    "rdt" => ["rdt","url"],
    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ["email", "token", "password","password_repeat",'CSRF_token','form']
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
// verify token
$otp = new OTP\Email($mailgun_api_domain, $mailgun_api_key);
if (!$otp->verify($params['email'], $params['token'])) {
  echo \json_encode([
    "status" => "2.1",
    "errors" => ["Failed to validate request token."],
    "message" => "Request halted."
  ]);
  exit;
}

if( !$user = (new MultiForm(MYSQL_ADMIN_DB, "user", "_id"))->findBySql("SELECT * FROM :db:.:tbl: WHERE email='{$database->escapeValue($params['email'])}' AND status='PENDING' LIMIT 1") ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Invalid account [status], login to change your password."],
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
$conn = new MySQLDatabase(MYSQL_SERVER, MYSQL_DEVELOPER_USERNAME, MYSQL_DEVELOPER_PASS);
if (!$conn->query("UPDATE {$admin_db}.`user` SET password='{$conn->escapeValue($pwd)}', status='ACTIVE' WHERE email = '{$conn->escapeValue($params['email'])}' LIMIT 1")) {
  echo \json_encode([
    "status" => "4.1",
    "errors" => ["Failed to create password, please try again later."],
    "message" => "Request failed."
  ]);
  exit;
}
unset($conn);
$rdt = !empty($params['rdt']) ? $params['rdt'] : WHOST . "/dashboard";
echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!",
  "rdt" => Generic::setGet(WHOST . "/admin/sign-in", ["rdt"=>$rdt])
]);
exit;
