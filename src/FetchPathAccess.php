<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login(false);
\check_access("/path-accesses", false, "project-admin");

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
$required = ["user", "domain"];
$params = [
    "user"   => ["user","username",3,12, [], "MIXED"],
    "domain" => ["domain","username",3,128,[],'LOWER',['-','.','_']],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ];
if (!$http_auth) {
  $required[] = "form";
  $required[] = "CSRF_token";
}
$params = $gen->requestParam($params, $post, $required);
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

$data = new MultiForm(MYSQL_ADMIN_DB, 'path_access','id');
$base_db = MYSQL_BASE_DB;
$query =
"SELECT pa.id,pa.user, pa._author, pa._created,
        wp.name, wp.domain,wp.path,wp.`access_rank`, wp.title,wp.icon,wp.description,
        CONCAT(au.name,' ',au.surname) AS user_name
 FROM :db:.:tbl: AS pa ";
 $join = "LEFT JOIN :db:.work_path AS wp ON wp.name=pa.path_name
          LEFT JOIN :db:.user AS au ON au._id=pa.user";

$cond = " WHERE pa.path_name IN (
            SELECT name
            FROM :db:.work_path
            WHERE domain='{$db->escapeValue($params['domain'])}'
          )
          AND pa.user='{$db->escapeValue($params['user'])}'";

$count = $data->findBySql("SELECT COUNT(*) AS cnt FROM :db:.:tbl: AS pa {$cond} ");
// echo $db->last_query;
$count = $data->total_count = $count ? $count[0]->cnt : 0;

$limit = "0,512";
$query .= $join;
$query .= $cond;
$sort = " ORDER BY wp.`access_rank` ASC ";

$query .= $sort;
$query .= " LIMIT {$limit} ";

// echo \str_replace(':tbl:','path_access',\str_replace(':db:',MYSQL_ADMIN_DB,$query));
// exit;
$found = $data->findBySql($query);

if( !$found ){
  die( \json_encode([
    "message" => "Request completed.",
    "errors" => [],
    "status" => "0.2"
    ]) );
}
// process result
$result = [
];
foreach($found as $k=>$obj){
  unset($found[$k]->errors);
  unset($found[$k]->current_page);
  unset($found[$k]->per_page);
  unset($found[$k]->total_count);

  $found[$k]->access_rank = (int)$found[$k]->access_rank;
}

$result["message"] = "Request completed.";
$result["errors"] = [];
$result["status"] = "0.0";
$result["access"] = $found;

echo \json_encode($result);
exit;
