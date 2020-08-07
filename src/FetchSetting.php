<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login(false);
\check_access("/settings", false, "project-admin");

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
$params = $gen->requestParam(
  [
    "id" => ["id","int",1,0],
    "domain" => ["domain","username",3,72,[],'LOWER',['-','.']],
    "search" => ["search","text",3,25],
    "page" =>["page","int",1,0],
    "limit" =>["limit","int",1,0],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  []
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

$count = 0;
$data = new MultiForm(MYSQL_BASE_DB, 'setting_option','name');
$data->current_page = $page = (int)$params['page'] > 0 ? (int)$params['page'] : 1;
$usr_concat = "\\\\SYSTEM";
$query =
"SELECT sopt.name, sopt.domain, sopt.title, sopt.description,
        stt.id, stt.skey AS 'key', stt.sval AS 'value', stt._updated
 FROM :db:.:tbl: AS sopt ";
 $join = " LEFT JOIN :db:.setting AS stt ON stt.skey = sopt.name AND stt.user = CONCAT(sopt.domain,'{$usr_concat}') ";

$cond = " WHERE 1=1 ";
if (!empty($params['id'])) {
  $cond .= " AND sopt.name = (
    SELECT skey FROM :db:.setting
    WHERE id={$params['id']}
    LIMIT 1
  ) ";
}else{
  if (!empty($params['domain'])) {
    $cond .= " AND sopt.domain='{$params['domain']}' ";
  } if( !empty($params['search']) ){
    $params['search'] = $db->escapeValue(\strtolower($params['search']));
    $cond .= " AND (
      sopt.name = '{$params['search']}'
      OR sopt.domain = '{$params['search']}'
      OR LOWER(sopt.name) LIKE '%{$params['search']}%'
      OR LOWER(sopt.title) LIKE '%{$params['search']}%'
    ) ";
  }
}

$count = $data->findBySql("SELECT COUNT(*) AS cnt FROM :db:.:tbl: AS sopt {$cond} ");
// echo $db->last_query;
$count = $data->total_count = $count ? $count[0]->cnt : 0;

$data->per_page = $limit = !empty($params['id']) ? 1 : (
    (int)$params['limit'] > 0 ? (int)$params['limit'] : 35
  );
$query .= $join;
$query .= $cond;
$sort = " ORDER BY sopt.name ";

$query .= $sort;
$query .= " LIMIT {$data->per_page} ";
$query .= " OFFSET {$data->offset()}";

// echo \str_replace(':tbl:','setting_option',\str_replace(':db:',MYSQL_BASE_DB,$query));
// exit;
$found = $data->findBySql($query);
// $tym = new \TymFrontiers\BetaTym;

if( !$found ){
  die( \json_encode([
    "message" => "No setting(s) found for your query.",
    "errors" => [],
    "status" => "0.2"
    ]) );
}
// process result
$tym = new BetaTym;
$data_obj = new Data;
$result = [
  'records' => (int)$count,
  'page'  => $data->current_page,
  'pages' => $data->totalPages(),
  'limit' => $limit,
  'has_previous_page' => $data->hasPreviousPage(),
  'has_next_page' => $data->hasNextPage(),
  'previous_page' => $data->hasPreviousPage() ? $data->previousPage() : 0,
  'next_page' => $data->hasNextPage() ? $data->nextPage() : 0
];
foreach($found as $k=>$obj){
  unset($found[$k]->errors);
  unset($found[$k]->current_page);
  unset($found[$k]->per_page);
  unset($found[$k]->total_count);

  @ $found[$k]->id = (int)$found[$k]->id;
  $found[$k]->min_desc = $data_obj->getLen($found[$k]->description,72);
  $found[$k]->updated_date = $found[$k]->updated();
  $found[$k]->updated = !empty($found[$k]->updated()) ? $tym->MDY($found[$k]->updated()) : null;
}

$result["message"] = "Request completed.";
$result["errors"] = [];
$result["status"] = "0.0";
$result["settings"] = $found;

echo \json_encode($result);
exit;
