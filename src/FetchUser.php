<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login(false);
\check_access("/users", false, "project-admin");

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
    "id"          => ["id","username",3,12],
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
$data = new MultiForm(MYSQL_ADMIN_DB, 'user','_id');
$data->current_page = $page = (int)$params['page'] > 0 ? (int)$params['page'] : 1;
$data_db = MYSQL_DATA_DB;
$query =
"SELECT usr._id AS id, usr.status, usr.name, usr.surname, usr.work_group, usr.email, usr.phone,
        usr.country_code, usr.state_code, usr._author AS author, usr._created,
        CONCAT(ausr.name, ' ', ausr.surname) AS author_name,
        cy.name AS country,
        st.name AS state,
        wg.`rank` AS access_rank
 FROM :db:.:tbl: AS usr ";
 $join = " LEFT JOIN :db:.:tbl: AS ausr ON ausr._id = usr._author
           LEFT JOIN `{$data_db}`.`country` AS cy ON cy.code=usr.country_code
           LEFT JOIN `{$data_db}`.`state` AS st ON st.code = usr.state_code
           LEFT JOIN :db:.work_group AS wg ON wg.name = usr.work_group ";

$cond = " WHERE 1=1 ";
// $cond = " WHERE usr.work_group IN(
//             SELECT name
//             FROM :db:.work_group
//             WHERE `rank` <= {$rank}
//           ) ";
if (!empty($params['id'])) {
  $cond .= " AND usr._id='{$params['id']}' ";
}else{
  if( !empty($params['search']) ){
    $params['search'] = $db->escapeValue(\strtolower($params['search']));
    $cond .= " AND (
      LOWER(usr.name) LIKE '%{$params['search']}%'
      OR LOWER(usr.surname) LIKE '%{$params['search']}%'
      OR LOWER(usr.email) LIKE '%{$params['search']}%'
      OR LOWER(usr.phone) LIKE '%{$params['search']}%'
    ) ";
  }
}

$count = $data->findBySql("SELECT COUNT(*) AS cnt FROM :db:.:tbl: AS usr {$cond} ");
// echo $db->last_query;
$count = $data->total_count = $count ? $count[0]->cnt : 0;

$data->per_page = $limit = !empty($params['id']) ? 1 : (
    (int)$params['limit'] > 0 ? (int)$params['limit'] : 35
  );
$query .= $join;
$query .= $cond;
$sort = " ORDER BY usr.name ";

$query .= $sort;
$query .= " LIMIT {$data->per_page} ";
$query .= " OFFSET {$data->offset()}";

// echo \str_replace(':tbl:','user',\str_replace(':db:',MYSQL_ADMIN_DB,$query));
// exit;
$found = $data->findBySql($query);
$tym = new \TymFrontiers\BetaTym;
$data_obj =  new Data;
if( !$found ){
  die( \json_encode([
    "message" => "No user found for your query.",
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
  unset($found[$k]->password);
  unset($found[$k]->_id);

  $found[$k]->phone_local = (
    !empty($found[$k]->phone)
      ? $data_obj->phoneToLocal($found[$k]->phone, $found[$k]->country_code)
      : ""
    );
  $found[$k]->created_date = $found[$k]->created();
  $found[$k]->created = $tym->MDY($found[$k]->created());
}

$result["message"] = "Request completed.";
$result["errors"] = [];
$result["status"] = "0.0";
$result["users"] = $found;

echo \json_encode($result);
exit;
