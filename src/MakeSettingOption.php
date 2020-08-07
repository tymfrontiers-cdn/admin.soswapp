<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login(false);
\check_access("/setting-option", false, "project-admin");

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
  "id"    => ["id","int",1,0],
  "name"    => ["name","username",3,32,[],'UPPER',['-','.']],
  "domain"  => ["domain","username",3,128,[],'LOWER',['-','.']],
  "multi_val"  => ["multi_val","boolean"],
  "type" => ["type","option", \array_keys((new \TymFrontiers\Validator)->validate_type)],
  "type_variant" => ["type_variant","text",5,512],
  "title" => ["title","text",3,52],
  "description" => ["description","text",5,256],

  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
];
$req = [];
if (!$http_auth) {
  $req[] = 'form';
  $req[] = 'CSRF_token';
}
$is_new = empty($post['id']);
if ( $is_new ) {
  $req[] = "name";
  $req[] = "domain";
  $req[] = "type";
  $req[] = "title";
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
// check for duplicate
if ($is_new && (new MultiForm(MYSQL_BASE_DB, 'setting_option', 'id'))->findBySql("SELECT id FROM :db:.:tbl: WHERE name='{$database->escapeValue($params['name'])}' AND domain='{$database->escapeValue($params['domain'])}' LIMIT 1")){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Duplicate value for name/key."],
    "message" => "Request halted."
  ]);
  exit;
}
include PRJ_ROOT . "/src/Pre-Process.php";
$option = !$is_new
  ? (new MultiForm(MYSQL_BASE_DB, 'setting_option', 'id'))->findById($params['id'])
  : new MultiForm(MYSQL_BASE_DB, 'setting_option', 'id');

if ( !$option ) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Option -type with ID: '{$params['id']}' not found."],
    "message" => "Request halted."
  ]);
  exit;
}
foreach ($params as $k=>$v) {
  if (!empty($v)) $option->$k = $v;
}
$option->multi_val = (bool)$params['multi_val'] ? 1 : 0;
if (!$option->save()) {
  $do_errors = [];

  $option->mergeErrors();
  $more_errors = (new InstanceError($option,true))->get('',true);
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
