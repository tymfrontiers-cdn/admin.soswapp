<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login(false);
\check_access("/path-access", false, "project-admin");

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
  "id"          => ["id","int",1,0],
  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
];
$req = ['id'];
if (!$http_auth) {
  $req[] = 'form';
  $req[] = 'CSRF_token';
}
// if ( \trim($post['task']) == 'CREATE' ) {
//   $req[] = "icon";
//   $req[] = "path";
//   $req[] = "description";
// }

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
include PRJ_ROOT . "/src/Pre-Process.php";
// run process
$access = (new MultiForm(MYSQL_ADMIN_DB,'path_access','id'))->findById($params['id']);
if (!$access) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["No record was found for give [id] '{$params['id']}'"],
    "message" => "Request failed."
  ]);
  exit;
}
if (!$access->delete()) {
  echo \json_encode([
    "status" => "4.1",
    "errors" => ["Failed to delete at this time, try again later."],
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
