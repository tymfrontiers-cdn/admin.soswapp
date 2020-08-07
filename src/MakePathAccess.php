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
  "user"          => ["user","username",3,12],
  "author"          => ["author","username",3,12],
  "domain"          => ["domain","username",3,98,[],'LOWER',['-','.']],
  "path"          => ["path","text",1,1024],

  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
];
$req = ["user", "path", "domain"];
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
if (empty($params['author'])) $params['author'] = $session->name;
include PRJ_ROOT . "/src/Pre-Process.php";

if (!(new MultiForm(MYSQL_ADMIN_DB,'user','_id'))->findBySql("SELECT * FROM :db:.:tbl: WHERE _id='{$db->escapeValue($params['user'])}' AND status IN ('ACTIVE','PENDING') LIMIT 1")) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["No active [user] was found as '{$params['user']}'"],
    "message" => "Request halted"
  ]);
  exit;
}
$paths = [];
if (!empty($params['path']) ){
  $paths = \explode(',',$params['path']);
}
$allowed_access_r = [];
$allowed_access = (new MultiForm(MYSQL_ADMIN_DB,'work_path','name'))
->findBySql("SELECT wp.name,wp.domain,wp.path,wp.access_rank, wp.title, wp.icon, wp.description

             FROM :db:.:tbl: AS wp
             WHERE wp.domain='{$db->escapeValue($params['domain'])}'
             AND wp.access_rank <= (
               SELECT `rank`
               FROM :db:.work_group
               WHERE name = (
                 SELECT work_group
                 FROM :db:.user
                 WHERE _id ='{$db->escapeValue($params['user'])}'
                 LIMIT 1
               )
               LIMIT 1
             )");
 if ($allowed_access) {
   foreach ($allowed_access as $ac){
     $allowed_access_r[] = $ac->name;
   }
 }
 foreach ($paths as $i=>$val) {
   if (!\in_array($val,$allowed_access_r)) unset($paths[$i]);
 }
if ($paths) {
  $found = (new MultiForm(MYSQL_ADMIN_DB,'path_access','id'))
  ->findBySql("SELECT pa.id,pa.user,pa.path_name,
                wp.domain
                FROM :db:.:tbl: AS pa
                LEFT JOIN :db:.work_path AS wp ON wp.name = pa.path_name
                WHERE pa.user='{$db->escapeValue($params['user'])}'
                AND pa.path_name IN (
                  SELECT name
                  FROM :db:.work_path
                  WHERE domain = '{$db->escapeValue($params['domain'])}'
                )");
  // get old vals
  $old_access_r = [];

  if ($found) {
    foreach ($found as $fo) {
      $old_access_r[] = $fo->path_name;
    }
  }
  $db->query("DELETE FROM `" . MYSQL_ADMIN_DB ."`.`path_access`
              WHERE user = '{$db->escapeValue($params['user'])}'
              AND path_name NOT IN ('".\implode("','",$paths)."')
              AND path_name IN(
                SELECT name FROM `".MYSQL_ADMIN_DB."`.work_path
                WHERE domain = '{$db->escapeValue($params['domain'])}'
              )");
  foreach ($paths as $i=>$ac){
    if (\in_array($ac,$old_access_r)) {
      unset($paths[$i]);
    }
  }
  if (!empty($paths)){
    $insert = "INSERT INTO `" . MYSQL_ADMIN_DB . "`.`path_access` (user,path_name,_author) VALUES ";
    $insert_r = [];
    foreach ($paths as $acc) {
      $insert_r[] = "('{$params['user']}','{$db->escapeValue($acc)}','{$db->escapeValue($params['author'])}')";
    }
    $insert .= \implode(",",$insert_r);
    if( !$db->query($insert) ){
      echo \json_encode([
        "status" => "4.1",
        "errors" => ["Failed to update addmin path/access, try again later."],
        "message" => "Request failed."
      ]);
      exit;
    }
  }
}

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!"
]);
exit;
