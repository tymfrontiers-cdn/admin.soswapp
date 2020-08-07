<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;

$data = new Data;
\header("Content-Type: application/json");
$post = \json_decode( \file_get_contents('php://input'), true); // json data
$post = !empty($post) ? $post : (
  !empty($_POST) ? $_POST : (
    !empty($_GET) ? $_GET : []
    )
);
$gen = new Generic;
$auth = new API\Authentication ($api_sign_patterns);
$http_auth = $auth->validApp ();
if( !$http_auth && ( empty($post['form']) || empty($post['CSRF_token']) ) ){
  HTTP\Header::unauthorized (false,'', Generic::authErrors ($auth,"Request [Auth-App]: Authetication failed.",'self',true));
}

$params = $gen->requestParam(
  [
    "reference" =>["reference","text", 3, 0],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ["reference", "CSRF_token", "form"]
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
//
$otp = new OTP\Email($mailgun_api_domain, $mailgun_api_key);
if (!$otp->resend($params['reference'])) {
  echo \json_encode([
    "status" => "5.1",
    "errors" => ["We could not resend email at this time, try again later."],
    "message" => "Request failed."
  ]);
  exit;
}
echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Check now, we have resent it."
]);
exit;
