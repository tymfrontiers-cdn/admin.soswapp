<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login(false);
\check_access("/work-paths", false, "project-admin");

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
    "domain" => ["domain","username",3,128,[],'LOWER',['-','.']],
    "search" => ["search","text",3,25],
    "access_rank" =>["access_rank","int", 0, 14],
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
$data = new MultiForm(MYSQL_ADMIN_DB, 'work_path','name');
$data->current_page = $page = (int)$params['page'] > 0 ? (int)$params['page'] : 1;
$query =
"SELECT wp.name, wp.domain, wp.path, wp.type, wp.nav_visible, wp.access_rank, wp.icon,
        wp.access_rank_strict, wp.onclick, wp.classname, wp.title,
        wp.sort, wp.description, wp._created,
        wg.name AS min_access
 FROM :db:.:tbl: AS wp ";
 $join = " LEFT JOIN :db:.work_group AS wg ON wg.`rank` = wp.`access_rank` ";

$cond = " WHERE 1=1 ";
if (!empty($params['name'])) {
  $cond .= " AND wp.name='{$params['name']}' ";
} else {
  if( !empty($params['domain']) ){
    $cond .= " AND wp.domain = '{$params['domain']}' ";
  }
  if( isset($params['access_rank']) && $params['access_rank'] !== "" ){
    $cond .= " AND  (
      (wp.access_rank_strict = TRUE AND wp.access_rank = {$params['access_rank']}) OR (
        wp.access_rank <= {$params['access_rank']}
      )
    ) ";
  }
  if( !empty($params['search']) ){
    $params['search'] = $db->escapeValue(\strtolower($params['search']));
    $cond .= " AND (
      wp.name = '{$params['search']}'
      OR LOWER(wp.title) LIKE '%{$params['search']}%'
      OR LOWER(wp.`path`) LIKE '%{$params['search']}%'
      OR LOWER(wp.domain) = '{$params['search']}'
    ) ";
  }
}

$count = $data->findBySql("SELECT COUNT(*) AS cnt FROM :db:.:tbl: AS wp {$cond} ");
// echo $db->last_query;
$count = $data->total_count = $count ? $count[0]->cnt : 0;

$data->per_page = $limit = !empty($params['name']) ? 1 : (
    (int)$params['limit'] > 0 ? (int)$params['limit'] : 35
  );
$query .= $join;
$query .= $cond;
$sort = " ORDER BY wp.sort ASC, wp.`access_rank` DESC ";

$query .= $sort;
$query .= " LIMIT {$data->per_page} ";
$query .= " OFFSET {$data->offset()}";

// echo \str_replace(':tbl:','work_path',\str_replace(':db:',MYSQL_ADMIN_DB,$query));
// exit;
$found = $data->findBySql($query);
$tym = new \TymFrontiers\BetaTym;

if( !$found ){
  die( \json_encode([
    "message" => "No path found for your query.",
    "errors" => [],
    "status" => "0.2"
    ]) );
}
// process result
$tym = new \TymFrontiers\BetaTym;

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

  $found[$k]->nav_visible = (bool)$found[$k]->nav_visible;
  $found[$k]->access_rank = (int)$found[$k]->access_rank;
  $found[$k]->access_rank_strict = (bool)$found[$k]->access_rank_strict;
  $found[$k]->sort = (int)$found[$k]->sort;

  $found[$k]->created_date = $found[$k]->created();
  $found[$k]->created = $tym->MDY($found[$k]->created());
}

$result["message"] = "Request completed.";
$result["errors"] = [];
$result["status"] = "0.0";
$result["paths"] = $found;

echo \json_encode($result);
exit;
