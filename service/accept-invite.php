<?php
namespace TymFrontiers;
require_once "../.appinit.php";
require_once APP_BASE_INC;
require_once APP_ROOT . "/src/Helper.php";
// \require_login(true);
$gen = new Generic;
$data = new Data;
$params = $gen->requestParam([
  "rdt" => ["rdt","url"],
  "user" => ["user","text", 5, 0],
  "token" => ["token","text", 5, 0],
  "reference" => ["reference","text", 5, 0]
],'get',["reference"]);
if (!$params) HTTP\Header::badRequest(true, "You have followed a bad or broken link.");
if (!$reference = $data->decodeDecrypt($params['reference'])) {
  HTTP\Header::badRequest(true, "Invalid request reference.");
}
if (!$token = $data->decodeDecrypt($params['token'])) {
  HTTP\Header::badRequest(true, "Invalid request token.");
}
$email = $data->decodeDecrypt($params['user']);
if (!$email || !$user = (new MultiForm(MYSQL_ADMIN_DB, "user", "_id"))->findBySql("SELECT * FROM :db:.:tbl: WHERE email='{$database->escapeValue($email)}' AND status='PENDING' LIMIT 1") ) {
  HTTP\Header::badRequest(true, "Invalid request user/email.");
}
$user = $user[0];
$otp = new OTP\Email($mailgun_api_domain, $mailgun_api_key);
if (!$otp->verify($email, $token)) {
  HTTP\Header::unauthorized(true, "You have followed an invalid link.");
}
// verify and go
$success = false;
$base_db = MYSQL_BASE_DB;
$rdt = !empty($params['rdt']) ? $params['rdt'] : WHOST . "/dashboard";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" manifest="/site.webmanifest">
  <head>
    <meta charset="utf-8">
    <title>Create login password | <?php echo PRJ_TITLE; ?></title>
    <?php include PRJ_INC_ICONSET; ?>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
    <meta name="author" content="<?php echo PRJ_AUTHOR; ?>">
    <meta name="creator" content="<?php echo PRJ_CREATOR; ?>">
    <meta name="publisher" content="<?php echo PRJ_PUBLISHER; ?>">
    <meta name="robots" content='nofollow'>
    <!-- Theming styles -->
    <link rel="stylesheet" href="/app/soswapp/font-awesome.soswapp/css/font-awesome.min.css">
    <link rel="stylesheet" href="/app/soswapp/theme.soswapp/css/theme.min.css">
    <link rel="stylesheet" href="/app/soswapp/theme.soswapp/css/theme-<?php echo PRJ_THEME; ?>.min.css">
    <!-- optional plugin -->
    <link rel="stylesheet" href="/app/soswapp/plugin.soswapp/css/plugin.min.css">
    <link rel="stylesheet" href="/app/soswapp/dnav.soswapp/css/dnav.min.css">
    <link rel="stylesheet" href="/app/soswapp/faderbox.soswapp/css/faderbox.min.css">
    <!-- Project styling -->
    <link rel="stylesheet" href="<?php echo \html_style("base.css"); ?>">
  </head>
  <body>
    <?php \setup_page("/new-user", PRJ_DOMAIN, true, PRJ_HEADER_HEIGHT); ?>
    <?php include PRJ_INC_HEADER; ?>

    <section id="main-content">
      <div class="view-space">
        <div class="grid-12-tablet">
          <div class="sec-div padding -p20 color blue">
            <h1 class="color-text align-c"> <i class="fas fa-key fa-border"></i> Set your password</h1>
            <p>Dear <b><?php echo $user->name; ?></b>, <br> You are required to create your new login password to enable you start using this platform.</p>
            <p>Only you can login to your account, ensure not to share your login detail with any team/other users.</p>
            <p>
              <b>This is not me!</b> If you think this message does not address you and [<?php echo $user->email ?>] is not your email address, Kindly ignore this process  and <a href="../">head back to home page</a>
            </p>
          </div>
        </div>
        <div class="grid-7-tablet">
          <div class="sec-div padding -p20">
            <span class="fa-stack fa-2x push-left">
              <i class="far fa-stack-2x fa-circle"></i>
              <i class="fas fa-stack-1x fa-user-shield"></i>
            </span>
            <h3>Account Safety and password tips</h3>
            <p>Your Account Safety starts with your password. Avoid using weak &amp; easily guessable combinations for password.</p>
            <h3>Here are some guide to help you create new password</h3>
            <ul>
              <li>Your password should contain at least one upper case character and lower case character</li>
              <li>Include at least one/more numeric value.</li>
              <li>Also include one/more of these special characters:[</span> <code style="color: red; font-weight:bold">!@#$%^&*()/-_=+{}[];:,<.></code> ]</li>
              <li>A strong should be between 8-24 character length.</li>
              <li>Avoid using your name, phone number, email, date of birth and other personal detail in password combination.</li>
            </ul>
            <p>Build the habit of using strong/random password every time. Remember it is easier to prevent security breach than recovering from it.</p>
          </div>
        </div>
        <div class="grid-5-tablet">
          <form
            id="new-user-form"
            class="block-ui padding -p20 color blue bg-white drop-shadow"
            method="post"
            action="/app/tymfrontiers-cdn/admin.soswapp/src/CreateUserPass.php"
            data-validate="false"
            onsubmit="sos.form.submit(this,doSave);return false;"
          >
            <input type="hidden" name="email" value="<?php echo $user->email; ?>">
            <input type="hidden" name="token" value="<?php echo $token; ?>">
            <input type="hidden" name="rdt" value="<?php echo $rdt; ?>">
            <input type="hidden" name="form" value="new-user-form">
            <input type="hidden" name="CSRF_token" value="<?php echo ($session->createCSRFtoken("new-user-form"));?>">
            <h2>Your new password</h2>
            <div class="grid-12-tablet">
              <label for="password"><i class="fas fa-asterisk fa-border fa-sm"></i> New password</label>
              <input type="password" name="password" autocomplete="off" placeholder="Password" required id="password">
            </div>
            <div class="grid-12-tablet">
              <label for="password-repeat"><i class="fas fa-asterisk fa-border fa-sm"></i> Repeat password</label>
              <input type="password" name="password_repeat" autocomplete="off" placeholder="Password" required id="password-repeat">
            </div>
            <div class="grid-7-tablet">
              <button type="submit" class="btn blue"> <i class="fas fa-save"></i> Save &amp; Login </button>
            </div>

            <br class="c-f">
          </form>
        </div>
        <br class="c-f">
      </div>
    </section>
    <?php include PRJ_INC_FOOTER; ?>
    <!-- Required scripts -->
    <script src="/app/soswapp/jquery.soswapp/js/jquery.min.js">  </script>
    <script src="/app/soswapp/js-generic.soswapp/js/js-generic.min.js">  </script>
    <script src="/app/soswapp/theme.soswapp/js/theme.min.js"></script>
    <!-- optional plugins -->
    <script src="/app/soswapp/plugin.soswapp/js/plugin.min.js"></script>
    <script src="/app/soswapp/dnav.soswapp/js/dnav.min.js"></script>
    <script src="/app/soswapp/faderbox.soswapp/js/faderbox.min.js"></script>
    <!-- project scripts -->
    <script src="<?php echo \html_script ("base.min.js"); ?>"></script>
    <script src="<?php echo WHOST . "/user/assets/js/user.min.js" ?>"></script>
    <script type="text/javascript">
      function doSave(resp) {
        if( resp && ( resp.errors.length <= 0 || resp.status == "0.0") ){
          if ( resp.rdt.length > 0 ) {
            setTimeout(function(){ window.location = resp.rdt; },3200);
          } else {
            setTimeout(function(){ removeAlert(); window.location = location.origin + "/dashboard"; },3200);
          }
        }
      }
    </script>
  </body>
</html>
