<?php
namespace TymFrontiers;
use \SOS\User;

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

$gen = new Generic();
$params = [
  "id" =>["id","int"],
  "email" =>["email","email"],
  "MUST_EXIST" =>["MUST_EXIST","boolean"],
  "MUST_NOT_EXIST" =>["MUST_NOT_EXIST","boolean"],
  "code_variant" =>["code_variant","option",[
    Data::RAND_MIXED,
    Data::RAND_NUMBERS,
    Data::RAND_LOWERCASE,
    Data::RAND_UPPERCASE,
    Data::RAND_MIXED_LOWER,
    Data::RAND_MIXED_UPPER,
    ]],
    "code_length" =>["code_length","int", 8, 16],

  "form" => ["form","text",2,55],
  "CSRF_token" => ["CSRF_token","text",5,500]
];
$reqd = ["email"];
if( !$http_auth ){
  $reqd[] = "CSRF_token";
  $reqd[] = "form";
}
$params = $gen->requestParam($params, $post, $reqd);

if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError($gen,true))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request halted"
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

$email = $params['email'];
// echo "<tt> <pre>";
// \print_r ($post);
// echo "</pre></tt>";
// exit;

if( (bool)$params['MUST_EXIST'] && !User::valExist($params['email'],"email") ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Email: [{$params['email']}] not in record."],
    "message" => "Request halted."
  ]);
  exit;
}
if( (bool)$params['MUST_NOT_EXIST'] && User::valExist($params['email'],"email") ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Email: [{$params['email']}] already in use."],
    "message" => "Request halted."
  ]);
  exit;
}
if (empty($params['code_length'])) $params['code_length'] = 8;
if (empty($params['code_variant'])) $params['code_variant'] = Data::RAND_MIXED_UPPER;

$otp = new OTP\Email($mailgun_api_domain, $mailgun_api_key, PRJ_AUTO_EMAIL, WHOST);
$reference = $otp->send($params['email'], $params['code_length'], $params['code_variant'], "", \strtotime("+1 Hour"));
if (!$reference) {
  $errors = (new InstanceError($otp))->get('send',true);
  $errors = !empty($errors) ? $errors : ["Failed to send OPT message"];
  die( \json_encode([
  "status" => "4." . \count($errors),
  "errors" => $errors,
  "message" => "Request incomplete."
  ]));
}

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "OTP code has been sent to your email..",
  "reference" => $reference,
  "email" => $email
]);
exit;
