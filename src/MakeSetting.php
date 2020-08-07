<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login(false);
\check_access("/setting", false, "project-admin");

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
  "key"    => ["key","username",3,32,[],'UPPER',['-','.']],
  "domain"  => ["domain","username",3,128,[],'LOWER',['-','.']],

  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
];
if( $post ):
  if( !empty($post['key']) && !empty($post["domain"]) ){
    $key_prop = (new MultiForm(MYSQL_BASE_DB,'setting_option','id'))
    ->findBySql("SELECT name, type, type_variant, title, description
                 FROM :db:.:tbl:
                 WHERE name='{$database->escapeValue($post['key'])}'
                 AND domain='{$database->escapeValue($post['domain'])}'
                 LIMIT 1");
   if ($key_prop) {
     $key_prop = $key_prop[0];
     $typev = empty($key_prop->type_variant) ? false : Helper\setting_variant($key_prop->type_variant);
     $filt_arr = ["value", $key_prop->type];
     if (\in_array($key_prop->type, ["username","text","html","markdown","mixed","script","date","time","datetime","int","float"])) {
       $filt_arr[2] = !empty($typev["minval"]) ? $typev["minval"] : 0;
       $filt_arr[3] = !empty($typev["maxval"]) ? $typev["maxval"] : 0;
     } if ($key_prop->type == "option" && !empty($typev["optiontype"]) && $typev["optiontype"]=="checkbox") {
       $filt_arr[1] = "text";
       $filt_arr[2] = 3;
       $filt_arr[3] = 127;
     } if ($key_prop->type == "option" && !empty($typev["optiontype"]) && $typev["optiontype"]=="radio") {
       if (empty($typev["options"])) {
         echo \json_encode([
           "status" => "3.1",
           "errors" => ["No pre-set options, contact Developer"],
           "message" => "Request failed"
         ]);
         exit;
       }
       $filt_arr[2] = $typev["options"];
     }
     $rqp["value"] = $filt_arr;
   } else {
     echo \json_encode([
       "status" => "3.1",
       "errors" => ["Setting option not found for {$params['domain']}\\{$params['key']}"],
       "message" => "Request failed"
     ]);
     exit;
   }
  }
endif;

$req = [];
if (!$http_auth) {
  $req[] = 'form';
  $req[] = 'CSRF_token';
}
$is_new = empty($post['id']);
if ( $is_new ) {
  $req[] = "key";
  $req[] = "domain";
  $req[] = "value";
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

// use helper to set value
try {
  $is_set = Helper\setting_set_value("SYSTEM", $params["key"], $params["value"], $params["domain"]);
} catch (\Exception $e) {
  echo \json_encode([
    "status" => "4.1",
    "errors" => [$e->getMessage()],
    "message" => "Request failed."
  ]);
  exit;
}

// $setting = !$is_new
//   ? (new MultiForm(MYSQL_BASE_DB, 'setting', 'id'))->findById($params['id'])
//   : new MultiForm(MYSQL_BASE_DB, 'setting', 'id');
//
// if ( !$setting ) {
//   echo \json_encode([
//     "status" => "3.1",
//     "errors" => ["Setting with ID: '{$params['id']}' not found."],
//     "message" => "Request halted."
//   ]);
//   exit;
// }
// // var_dump($key_prop->type);
// // exit;
// $admin_db = MYSQL_BASE_DB;
// $set_user = $database->escapeValue("SYSTEM.{$params["domain"]}");
// $set_skey = $database->escapeValue($params["key"]);
// if ($key_prop->type == "boolean") {
//   $set_sval = (bool)$params["value"] ? 1 : 0;
// } else {
//   $set_sval = $database->escapeValue($params["value"]);
// }
// // var_dump($set_sval);
// // var_dump($key_prop->type);
// // exit;
//
// if ($is_new) {
//   $query = "INSERT INTO `{$admin_db}`.`setting` (`user`, `skey`, `sval`) VALUES ('{$set_user}', '{$set_skey}', '{$set_sval}')";
// } else {
//   $query = "UPDATE `{$admin_db}`.setting SET sval='{$set_sval}' WHERE id={$params['id']} LIMIT 1";
// }
// if (!$database->query($query)) {
//   $do_errors = [];
//   $more_errors = (new InstanceError($database,true))->get('',true);
//   if (!empty($more_errors)) {
//     foreach ($more_errors as $method=>$errs) {
//       foreach ($errs as $err){
//         $do_errors[] = $err;
//       }
//     }
//     echo \json_encode([
//       "status" => "4." . \count($do_errors),
//       "errors" => $do_errors,
//       "message" => "Request incomplete."
//     ]);
//     exit;
//   } else {
//     echo \json_encode([
//       "status" => "0.1",
//       "errors" => [],
//       "message" => "Request completed with no changes made."
//     ]);
//     exit;
//   }
// }

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!"
]);
exit;
