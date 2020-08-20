<?php
namespace TymFrontiers;
use \Mailgun\Mailgun,
    \Michelf\Markdown;
require_once "../.appinit.php";
require_once APP_BASE_INC;

\header("Content-Type: application/json");
\require_login(false);
\check_access("/user", false, "project-admin");

$data = new Data;
$post = \json_decode( \file_get_contents('php://input'), true); // json data
$post = !empty($post) ? $post : (
  !empty($_POST) ? $_POST : []
);
if( !empty($post['phone']) || !empty($post['country_code']) ){
  $post['phone'] = $data->phoneToIntl(\trim($post['phone']),\trim($post['country_code']));
}
// echo "<tt> <pre>";
// \print_r($post);
// echo "</pre></tt>";
// exit;
$gen = new Generic;
$auth = new API\Authentication ($api_sign_patterns);
$http_auth = $auth->validApp ();
if ( !$http_auth && ( empty($post['form']) || empty($post['CSRF_token']) ) ){
  HTTP\Header::unauthorized (false,'', Generic::authErrors ($auth,"Request [Auth-App]: Authetication failed.",'self',true));
}
$rqp = [
  "name"          => ["name","name"],
  "surname"       => ["surname","name"],
  "email"         => ["email","email"],
  "phone"         => ["phone","tel"],
  "country_code" =>["country_code","username",2,2],
  "state_code" =>["state_code","username",5,8],
  "work_group" =>["work_group","username",3,32],

  "note" => ["note","script",2, 528],
  "author" => ["author","username",3, 12],
  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
];
$req = [
  "name",
  "surname",
  "email",
  "phone",
  "country_code",
  "state_code",
  "work_group"
];
if (!$http_auth) {
  $req[] = 'form';
  $req[] = 'CSRF_token';
  $post['author'] = $session->name;
} else {
  $req[] = 'author';
}

$params = $gen->requestParam($rqp, $post, $req);
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
$admin = new \SOS\Admin($params['author']);
if (empty($admin->id())) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Request [author] could not be determined."],
    "message" => "Request halted."
  ]);
  exit;
}
$user = new MultiForm(MYSQL_ADMIN_DB, 'user', "_id");
if( $user->valExist($params['email'],"email") ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Email: [{$params['email']}] is not available"],
    "message" => "Request halted.",
    "rdt" => ""
  ]);
  exit;
}
if( $user->valExist($params['phone'],'phone') ){
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Phone: [{$params['phone']}] is not available"],
    "message" => "Request halted.",
    "rdt" => ""
  ]);
  exit;
}

include PRJ_ROOT . "/src/Pre-Process.php";

$otp_code = Data::uniqueRand('', 12, Data::RAND_MIXED, false);
$otp_ref = Data::uniqueRand('', 16, Data::RAND_MIXED_UPPER);
$otp_qid = NULL;
$rdt = WHOST . "/work-domain";
// $otp_expiry = NULL;
$otp_expiry = \strtotime("+1 Month", \time());
$otp_expiry = \strftime("%Y-%m-%d %H:%M:%S",$otp_expiry);

$auth_param = [
  "user" => $data->encodeEncrypt($params['email']),
  "token" => $data->encodeEncrypt($otp_code),
  "reference" => $data->encodeEncrypt($otp_ref),
  "rdt" => $rdt,
];
$whost = WHOST;
$prj_title = PRJ_TITLE;
$auth_link = Generic::setGet(WHOST . "/app/tymfrontiers-cdn/admin.soswapp/service/accept-invite.php", $auth_param);
$subject = "Invitation to join [{$prj_title}]";
$prj_icon = PRJ_EMAIL_ICON;
$prj_color_primary = PRJ_PRIMARY_COLOUR;
$prj_desc = PRJ_DESCRIPTION;

$params['note'] = !empty($params['note']) ? "<h3>Note</h3>" . Markdown::defaultTransform(\html_entity_decode($params['note'])) : "";
$invite_msg = <<<IVMSG
<header style="border-bottom: solid 5px {$prj_color_primary}; padding: 12px; margin-bottom: 8px; position:relative; height:auto">
  <a href="{$whost}"><img style="max-width:40%; max-height:72px; margin:0 0 3px 3px; float:right" src="{$prj_icon}" alt="Logo"></a>
  <br style="float:none; clear:both; padding:0; margin:0; height:0px;">
  <h2>{$subject}</h2>
  <p>$prj_desc</p>
</header>
<br style="float:none; clear:both; padding:0; margin:0; height:0px;">

<section>
  <p>Hi {$params['name']}, <br> <br> You were invited to join <b>{$prj_title}</b> by {$admin->name} {$admin->surname}.</p>
  {$params['note']}
  <p>Please follow the link below to accept this invitation.</p>
  <p><a href="{$auth_link}"
  style="font-weight:bold;
  display:inline-block;
  padding:12px 15px;
  background-color:#e4e4e4;
  color:black;
  border:solid 3px #cbcbcb;
  text-decoration:none;
  -webkit-border-radius:5px;
  -ms-border-radius:5px;
  -moz-border-radius:5px;
  border-radius:5px;">Accept Invitation</a></p>
</section>
IVMSG;
$message = $invite_msg;
$message_text = "Hi {$params['name']}, Kindly follow link: {$auth_link} to join {$prj_title}.";
// send message right away
if (empty($mailgun_api_key) || empty($mailgun_api_domain)) {
  echo \json_encode([
    "status" => "5.1",
    "errors" => ["Please contact developer to create Mailgun API and assign \$mailgun_api_domain and \$mailgun_api_key respectively."],
    "message" => "Variable error."
  ]);
  exit;
}
try {
  $mgClient = Mailgun::create($mailgun_api_key);
  $result = $mgClient->messages()->send($mailgun_api_domain, [
    'from' => PRJ_SUPPORT_EMAIL,
    'to' => "{$params['name']} {$params['surname']} <{$params['email']}>",
    'subject' => $subject,
    'text' => $message_text,
    'html' => $message
  ]);
  if(
    \is_object($result) &&
    !empty($result->getId()) &&
    \strpos($result->getId(), $mailgun_api_domain) !== false
  ){
    $otp_qid = $result->getId();
  }
} catch (\Exception $e) {
  echo \json_encode([
    "status" => "5.2",
    "errors" => ["We were unable to mail the user email entered, confirm that you have entered a correct receiving email and try again.", $e->getMessage()],
    "message" => "Communication error.",
    "rdt" => ""
  ]);
  exit;
}
// save detail
$otp = new MultiForm(MYSQL_LOG_DB, 'otp_email', 'id');
$otp->ref = $otp_ref;
$otp->user = $params['email'];
$otp->qid = $otp_qid;
$otp->code = $otp_code;
$otp->expiry = $otp_expiry;
$otp->subject = $subject;
$otp->message = $message;
$otp->message_text = $message_text;
$otp->sender = PRJ_SUPPORT_EMAIL;
$otp->receiver = "{$params['name']} {$params['surname']} <{$params['email']}>";
if (!$otp->create()) {
  if (!empty($otp->errors['query'])) {
    $errs = (new InstanceError($otp, true))->get("query",true);
    echo \json_encode([
      "status" => "4." . \count($errs),
      "errors" => $errs,
      "message" => "Communication error.",
      "rdt" => ""
    ]);
    exit;
  }
}
// register admin
$user->_id = $data->uniqueRand("", 12, $data::RAND_MIXED_UPPER, false, MYSQL_ADMIN_DB, "user", "_id");
$user->email = $params["email"];
$user->phone = $params["phone"];
$user->name = $params["name"];
$user->surname = $params["surname"];
$user->work_group = $params["work_group"];
$user->country_code = $params["country_code"];
$user->state_code = $params["state_code"];
$user->status = "PENDING";
if (!$user->create()) {
  $do_errors = [];

  $user->mergeErrors();
  $more_errors = (new InstanceError($user))->get('',true);
  if (!empty($more_errors)) {
    foreach ($more_errors as $method=>$errs) {
      foreach ($errs as $err){
        $do_errors[] = $err;
      }
    }
    echo \json_encode([
      "status" => "4." . \count($do_errors),
      "errors" => $do_errors,
      "message" => "Request incomplete."
    ]);
    exit;
  } else {
    echo \json_encode([
      "status" => "0.1",
      "errors" => [],
      "message" => "Request completed with no changes made."
    ]);
    exit;
  }
}

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!",
  "rdt" => Generic::setGet("/app/tymfrontiers-cdn/admin.soswapp/service/path-accesses.php", ["user"=>$user->_id, "domain"=>"project-admin"])
]);
exit;
