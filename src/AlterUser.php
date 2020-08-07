<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login(false);
\check_access("/user", false, "project-admin");

$post = \json_decode( \file_get_contents('php://input'), true); // json data
$post = !empty($post) ? $post : (
  !empty($_POST) ? $_POST : []
);
$gen = new Generic;
$auth = new API\Authentication ($api_sign_patterns);
$http_auth = $auth->validApp ();
if ( !$http_auth && ( empty($post['form']) || empty($post['CSRF_token']) ) ){
  HTTP\Header::unauthorized (false,'', Generic::authErrors ($auth,"Request [Auth-App]: Authetication failed.",'self',true));
}
$rqp = [
  "id"          => ["id","username",3,12],
  "status"          => ["status","option",["ACTIVE","BANNED","SUSPENDED","DISABLED"]],
  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
];
$req = ['id',"status"];
if (!$http_auth) {
  $req[] = 'form';
  $req[] = 'CSRF_token';
}

$params = $gen->requestParam($rqp,"post",$req);
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
$stat_list = [
  "PENDING" => ["SUSPENDED", "BANNED", "DISABLED"],
  "ACTIVE" => ["SUSPENDED", "BANNED", "DISABLED"],
  "SUSPENDED" => ["ACTIVE", "BANNED", "DISABLED"]
];
$user = (new MultiForm(MYSQL_ADMIN_DB, "user", '_id'))->findById($params['id']);
if ( !$user ) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["[user] ({$params['id']}) not found."],
    "message" => "Request halted."
  ]);
  exit;
}
if (!\array_key_exists($user->status, $stat_list) ) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["User does not have changeable status"],
    "message" => "Nothing to do here."
  ]);
  exit;
}
if (!\in_array($params["status"], $stat_list[$user->status]) ) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Invalid status type."],
    "message" => "Request halted."
  ]);
  exit;
}
// echo "here";
include PRJ_ROOT . "/src/Pre-Process.php";
// run process
$conn = new MySQLDatabase(MYSQL_SERVER, MYSQL_DEVELOPER_USERNAME, MYSQL_DEVELOPER_PASS);
$admin_db = MYSQL_ADMIN_DB;
if( !$conn->query("UPDATE `{$admin_db}`.user SET status='{$conn->escapeValue($params['status'])}' WHERE _id='{$conn->escapeValue($user->_id)}' LIMIT 1") ){
  $do_errors = [];
  $more_errors = (new InstanceError($conn))->get('',true);
  if (!empty($more_errors)) {
    foreach ($more_errors as $method=>$errs) {
      foreach ($errs as $err){
        $do_errors[] = $err;
      }
    }
    echo \json_encode([
      "status" => "4." . \count($do_errors),
      "errors" => $do_errors,
      "message" => "Request incomplete."
    ]);
    exit;
  } else {
    echo \json_encode([
      "status" => "0.1",
      "errors" => [],
      "message" => "Request completed with no changes made."
    ]);
    exit;
  }
}

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!"
]);
exit;
