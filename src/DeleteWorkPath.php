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
  "name"          => ["name","username",3,98,[],'LOWER',['-','.', '_', '/']],
  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
];
$req = ['name'];
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
$path = (new MultiForm(MYSQL_ADMIN_DB, "work_path",'name'))->findById($params['name']);
if( !$path ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["[workpath] ({$params['name']}) not found."],
    "message" => "Request halted."
  ]);
  exit;
}
$pathname = $params['name'];
if( !$path->delete() ){
  $do_errors = [];
  $path->mergeErrors();
  $more_errors = (new InstanceError($path))->get('',true);
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
// delete user work access setting
$db->query("DELETE FROM ".MYSQL_ADMIN_DB.".path_access WHERE path_name ='{$db->escapeValue($params['name'])}'");

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!"
]);
exit;
