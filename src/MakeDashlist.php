<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login(false);
\check_access("/user-dashlist", false, "project-admin");

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
  "id"    => ["id","int"],
  "path" => ["path","text",1,256],
  "title" => ["title","text",3,56],
  "subtitle" => ["subtitle","text",3,72],
  "icon" => ["icon","text",2,72],
  "sort" => ["sort","int"],
  "onclick"  => ["onclick","username",3,32,[],'MIXED'],
  "classname"  => ["classname","username",3,128,[],'MIXED',['-',' ']],
  "description" => ["description","text",15,128],

  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
];
$req = [];
if (!$http_auth) {
  $req[] = 'form';
  $req[] = 'CSRF_token';
}

if ( empty($post['id'])) {
  $req[] = "title";
  $req[] = "path";
  $req[] = "icon";
  $req[] = "description";
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
include PRJ_ROOT . "/src/Pre-Process.php";


$dash = !empty($params['id'])
  ? (new MultiForm(MYSQL_BASE_DB, 'user_dashlist', 'id'))->findById($params['id'])
  : new MultiForm(MYSQL_BASE_DB, 'user_dashlist', 'id');

if ( !$dash ) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Dashlist with [id]: '{$params['id']}' not found."],
    "message" => "Request halted."
  ]);
  exit;
}
foreach ($params as $k=>$v) {
  if (!empty($v)) $dash->$k = $v;
}
if (!$dash->save()) {
  $do_errors = [];

  $dash->mergeErrors();
  $more_errors = (new InstanceError($dash,true))->get('',true);
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
