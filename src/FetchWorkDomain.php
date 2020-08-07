<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login(false);
\check_access("/work-domains", false, "project-admin");

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
    "name"          => ["name","username",3,98,[],'LOWER',['-','.']],
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
$data = new MultiForm(MYSQL_ADMIN_DB, 'work_domain','name');
$data->current_page = $page = (int)$params['page'] > 0 ? (int)$params['page'] : 1;
$query =
"SELECT wd.name, wd.acronym, wd.path, wd.icon, wd.description,
        (
          SELECT COUNT(*)
          FROM :db:.work_path
          WHERE domain=wd.name
        ) AS work_paths
 FROM :db:.:tbl: AS wd ";
 $join = "";

$cond = " WHERE 1=1 ";
// $cond = " WHERE wd.work_group IN(
//             SELECT name
//             FROM :db:.work_group
//             WHERE `rank` <= {$rank}
//           ) ";
if (!empty($params['name'])) {
  $cond .= " AND wd.name='{$params['name']}' ";
}else{
  if( !empty($params['search']) ){
    $params['search'] = $db->escapeValue(\strtolower($params['search']));
    $cond .= " AND LOWER(wd.name) LIKE '%{$params['search']}%' ";
  }
}

$count = $data->findBySql("SELECT COUNT(*) AS cnt FROM :db:.:tbl: AS wd {$cond} ");
// echo $db->last_query;
$count = $data->total_count = $count ? $count[0]->cnt : 0;

$data->per_page = $limit = !empty($params['name']) ? 1 : (
    (int)$params['limit'] > 0 ? (int)$params['limit'] : 35
  );
$query .= $join;
$query .= $cond;
$sort = " ORDER BY wd.name ";

$query .= $sort;
$query .= " LIMIT {$data->per_page} ";
$query .= " OFFSET {$data->offset()}";

// echo \str_replace(':tbl:','admin',\str_replace(':db:',ADMIN_DB,$query));
// exit;
$found = $data->findBySql($query);
// $tym = new \TymFrontiers\BetaTym;

if( !$found ){
  die( \json_encode([
    "message" => "No domains found for your query.",
    "errors" => [],
    "status" => "0.2"
    ]) );
}
// process result
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

}

$result["message"] = "Request completed.";
$result["errors"] = [];
$result["status"] = "0.0";
$result["domains"] = $found;

echo \json_encode($result);
exit;
