<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login(false);
\check_access("/work-path", false, "project-admin");

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
  "name"    => ["name","username",3,98,[],'LOWER',['-','.', '_', '/']],
  "domain"  => ["domain","username",3,128,[],'LOWER',['-','.']],
  "path" => ["path","text",1,72],
  "type" => ["type","option",['READ','ALTER']],
  "title" => ["title","text",3,56],
  "icon" => ["icon","script",3,128],
  "sort" => ["sort","int"],
  "access_rank" => ["access_rank","int",1,14],
  "access_rank_strict" => ["access_rank_strict","bool"],
  "nav_visible" => ["nav_visible","bool"],
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

if ( empty($post['name'])) {
  $req[] = "domain";
  $req[] = "title";
  $req[] = "path";
  $req[] = "type";
  $req[] = "access_rank";
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

$params['nav_visible'] = !empty($_POST['nav_visible']) ? (bool)$_POST['nav_visible'] : false;
$params['access_rank_strict'] = !empty($_POST['access_rank_strict']) ? (bool)$_POST['access_rank_strict'] : false;

$path = !empty($params['name'])
  ? (new MultiForm(MYSQL_ADMIN_DB, 'work_path', 'name'))->findById($params['name'])
  : new MultiForm(MYSQL_ADMIN_DB, 'work_path', 'name');

if ( !$path ) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Work [path] '{$params['path']}' not found."],
    "message" => "Request halted."
  ]);
  exit;
}
foreach ($params as $k=>$v) {
  if (!empty($v)) $path->$k = $v;
}
$path->nav_visible = $params['nav_visible'];
$path->access_rank_strict = $params['access_rank_strict'];
if (empty($params['name'])) $path->name = Data::uniqueRand("",12,Data::RAND_MIXED_LOWER, false);;
$done = empty($params['name'])
  ? $path->create()
  : $path->update();
if (!$done) {
  $do_errors = [];

  $path->mergeErrors();
  $more_errors = (new InstanceError($path,true))->get('',true);
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
