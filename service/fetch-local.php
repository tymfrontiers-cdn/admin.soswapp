<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
$validator = new Validator();
$gen = new Generic;
// check for parameters input
$params = [
  "type" =>["type","text",2,25],
  "code" => ["code","text",2,12]
];
$reqd = ["type"];
// if( empty($post['id']) ){
// }
$post = !empty($_POST) ? $_POST : (
  !empty($_GET) ? $_GET : []
);
$params = $gen->requestParam(
  $params,
  $post,
  $reqd
);

if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError($gen,true))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request halted"
  ]);
  exit;
}
$data = new MultiForm(MYSQL_DATA_DB,$params['type'],'code');
$query = "SELECT code,name
          FROM :db:.:tbl: ";
if( \strtolower($params['type']) == 'state' ){
  $query .= " WHERE country_code='{$db->escapeValue($params['code'])}'";
}elseif( \in_array(\strtolower($params['type']),['city','lga']) ){
  $query .= " WHERE state_code='{$db->escapeValue($params['code'])}'";
}else{
}
$query .= " ORDER BY  LOWER(`name`) = '-other', `name` ASC";
$found = $data->findBySql($query);

if( !$found ){
  die( \json_encode([
    "message" => "No data was found.",
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
  if( isset($found[$k]->country_code) ) unset($found[$k]->country_code);
  if( isset($found[$k]->state_code) ) unset($found[$k]->state_code);
}

$result["message"] = "Request completed.";
$result["errors"] = [];
$result["status"] = "0.0";
$result["results"] = $found;

echo \json_encode($result);
exit;
